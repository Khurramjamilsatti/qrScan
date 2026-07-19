# Production deployment — Docker & Kubernetes

This guide covers running QRScan in production with **Docker Compose** (single host) or **Kubernetes** (cluster).

## Architecture

```
                    ┌─────────────────┐
   Internet ───────►│  Ingress / LB   │
                    └────────┬────────┘
                             │
                    ┌────────▼────────┐
                    │  web (nginx)    │  Vue SPA + proxy /api, /storage
                    └────────┬────────┘
                             │
              ┌──────────────┼──────────────┐
              │              │              │
     ┌────────▼────────┐     │     ┌────────▼────────┐
     │  api (Laravel)  │     │     │ queue worker    │
     └────────┬────────┘     │     └────────┬────────┘
              │              │              │
              └──────────────┼──────────────┘
                             │
                    ┌────────▼────────┐
                    │   PostgreSQL    │
                    └─────────────────┘
```

| Component | Image | Port | Role |
|-----------|-------|------|------|
| `web` | `qrscan-web` | 8080 | Serves built Vue app; proxies API |
| `api` | `qrscan-api` | 8080 | Laravel API + `/storage` files |
| `queue` | `qrscan-api` | — | `php artisan queue:work` |
| `postgres` | `postgres:16-alpine` | 5432 | Database |

---

## Prerequisites

- Docker 24+ and Docker Compose v2
- For Kubernetes: `kubectl`, a cluster (AKS, EKS, GKE, k3s, etc.), and an ingress controller (nginx recommended)
- Domain pointed at your load balancer (e.g. `qrscan.digital`)
- TLS: [cert-manager](https://cert-manager.io/) with Let's Encrypt (K8s) or your platform's certificate manager

---

## 1. Build images

Images are built from the **repository root** (not from `backend/` or `frontend/` alone).

```bash
chmod +x scripts/build-images.sh
REGISTRY=ghcr.io/your-github-username TAG=v1.0.0 ./scripts/build-images.sh
```

Or manually:

```bash
docker build -f backend/Dockerfile -t ghcr.io/your-org/qrscan-api:v1.0.0 .
docker build -f frontend/Dockerfile -t ghcr.io/your-org/qrscan-web:v1.0.0 .
```

Push to your registry:

```bash
docker push ghcr.io/your-org/qrscan-api:v1.0.0
docker push ghcr.io/your-org/qrscan-web:v1.0.0
```

---

## 2. Docker Compose (single-server production)

Best for a VPS or single VM when you do not need Kubernetes yet.

### 2.1 Configure environment

```bash
cp .env.docker-compose.example .env
cp backend/.env.docker.example backend/.env.docker
```

Use the **same** `DB_PASSWORD` in both files (avoid `!` and other special characters in passwords — they can break Docker env parsing).

Edit `backend/.env.docker`:

1. Set `APP_KEY` — generate with:
   ```bash
   docker compose -f docker-compose.prod.yml run --rm --no-deps --entrypoint php api artisan key:generate --show
   ```
2. Set `APP_URL` and `FRONTEND_URL` (e.g. `http://localhost:8080` for local Docker).
3. Set `SANCTUM_STATEFUL_DOMAINS` for your domain(s).
4. Add optional keys: Stripe, Hugging Face, mail SMTP, etc.

### 2.2 Start the stack

```bash
docker compose -f docker-compose.prod.yml up --build -d
```

Open **http://localhost:8080** (or put a reverse proxy in front for TLS).

### 2.3 First-time seed (optional)

```bash
docker compose -f docker-compose.prod.yml exec api php artisan db:seed --force
```

### 2.4 TLS in front of Compose

Put **Caddy**, **Traefik**, or **nginx** on the host to terminate HTTPS and proxy to `localhost:8080`. Example Caddyfile:

```
qrscan.digital {
    reverse_proxy localhost:8080
}
```

### 2.5 Operations

| Task | Command |
|------|---------|
| Logs | `docker compose -f docker-compose.prod.yml logs -f` |
| Migrate | `docker compose -f docker-compose.prod.yml exec api php artisan migrate --force` |
| Restart API | `docker compose -f docker-compose.prod.yml restart api` |
| Update | Rebuild images, then `docker compose -f docker-compose.prod.yml up -d --build` |

Volumes:

- `postgres_data` — database
- `api_storage` — uploaded files (`storage/app`)

---

## 3. Kubernetes deployment

Manifests live in `k8s/`. They assume:

- Namespace: `qrscan`
- Images: `ghcr.io/khurramjamilsatti/qrscan-api` and `qrscan-web`
- Ingress host: `qrscan.digital` (edit `k8s/configmap.yaml` and `k8s/ingress.yaml`)

### 3.1 Create secrets

```bash
cp k8s/secret.example.yaml k8s/secret.yaml
```

Edit `k8s/secret.yaml` with real values. **Do not commit `secret.yaml`.**

Generate `APP_KEY`:

```bash
docker run --rm -v "$PWD/backend":/app -w /app composer:2 \
  php -r "require 'vendor/autoload.php'; echo 'base64:'.base64_encode(random_bytes(32));"
```

Or run `php artisan key:generate --show` locally in `backend/`.

Apply:

```bash
kubectl apply -f k8s/secret.yaml
```

### 3.2 Configure ConfigMap

Edit `k8s/configmap.yaml`:

- `APP_URL`, `FRONTEND_URL` — your production URL
- `SANCTUM_STATEFUL_DOMAINS` — comma-separated hosts
- `CNAME_TARGET` — custom domain target for cards
- Mail, logging, etc. as needed

### 3.3 Set image names

Edit `k8s/kustomization.yaml` (or patch deployments) with your registry and tag:

```yaml
images:
  - name: ghcr.io/khurramjamilsatti/qrscan-api
    newName: ghcr.io/your-org/qrscan-api
    newTag: v1.0.0
  - name: ghcr.io/khurramjamilsatti/qrscan-web
    newName: ghcr.io/your-org/qrscan-web
    newTag: v1.0.0
```

### 3.4 Deploy

```bash
kubectl apply -k k8s/
```

Watch rollout:

```bash
kubectl -n qrscan get pods -w
```

### 3.5 Ingress & DNS

1. Install [nginx ingress controller](https://kubernetes.github.io/ingress-nginx/deploy/).
2. Install [cert-manager](https://cert-manager.io/docs/installation/) and a `ClusterIssuer` named `letsencrypt-prod` (referenced in `k8s/ingress.yaml`).
3. Point `qrscan.digital` A/AAAA records to the ingress load balancer IP.
4. TLS certificate is issued automatically via the ingress annotation.

### 3.6 Seed database (first deploy)

```bash
kubectl -n qrscan exec -it deploy/qrscan-api -- php artisan db:seed --force
```

### 3.7 Scaling notes

| Component | Default | Notes |
|-----------|---------|-------|
| `qrscan-web` | 2 replicas | Stateless; scale freely |
| `qrscan-api` | 1 replica | Uses `ReadWriteOnce` PVC for uploads |
| `qrscan-queue` | 1 replica | Increase for heavier job load |
| `postgres` | 1 StatefulSet | Use managed DB (RDS, Cloud SQL, Azure Database) for production HA |

**Multiple API replicas** require shared file storage (S3/Azure Blob + `FILESYSTEM_DISK=s3`) or an `ReadWriteMany` volume (NFS, EFS, Azure Files).

### 3.8 Managed PostgreSQL (recommended)

For production, replace the in-cluster Postgres StatefulSet with a managed service:

1. Create PostgreSQL 16+ with database `qrscan`.
2. Update `k8s/configmap.yaml`: `DB_HOST` = managed hostname.
3. Update secrets with credentials.
4. Remove or skip `postgres-*.yaml` from `kustomization.yaml`.
5. Redeploy.

### 3.9 Rolling updates

```bash
# Build & push new tag
REGISTRY=ghcr.io/your-org TAG=v1.0.1 ./scripts/build-images.sh
docker push ghcr.io/your-org/qrscan-api:v1.0.1
docker push ghcr.io/your-org/qrscan-web:v1.0.1

# Update kustomization tag, then:
kubectl apply -k k8s/
kubectl -n qrscan rollout status deploy/qrscan-api
kubectl -n qrscan rollout status deploy/qrscan-web
```

Migrations run automatically on API pod start (`RUN_MIGRATIONS=true` in ConfigMap).

---

## 4. Environment variables reference

| Variable | Required | Description |
|----------|----------|-------------|
| `APP_KEY` | Yes | Laravel encryption key |
| `APP_URL` | Yes | Public site URL (used in links, Sanctum) |
| `FRONTEND_URL` | Yes | Same as public URL when using single-domain setup |
| `DB_*` | Yes | PostgreSQL connection |
| `SANCTUM_STATEFUL_DOMAINS` | Yes | Domains that receive session cookies |
| `SESSION_DOMAIN` | Prod | Parent domain, e.g. `.qrscan.digital` |
| `QUEUE_CONNECTION` | Yes | `database` (default) |
| `FILESYSTEM_DISK` | Yes | `public` (local PVC) or `s3` for multi-replica |
| `CNAME_TARGET` | Optional | Custom domain CNAME for digital cards |
| `HUGGINGFACE_API_TOKEN` | Optional | AI image generation |
| Stripe keys | Optional | Billing |

---

## 5. Health checks

- API: `GET /up` (Laravel health route)
- Web: `GET /`

---

## 6. Troubleshooting

### API container keeps restarting

```bash
kubectl -n qrscan logs deploy/qrscan-api
# or
docker compose -f docker-compose.prod.yml logs api
```

Common causes: missing `APP_KEY`, database not reachable, migration failure.

### 502 on `/api/*`

- Confirm `api` service is running: `kubectl -n qrscan get svc api`
- From a `web` pod: `wget -qO- http://api:8080/up`

**Docker Compose:** if the landing page shows *"Unable to load landing page content"*, the API is usually down. Check:

```bash
docker compose -f docker-compose.prod.yml logs api
```

If you see `password authentication failed for user "qrscan"`, the Postgres volume was created with a different password than `DB_PASSWORD` in `.env` / `backend/.env.docker`. Reset the database volume (destroys local DB data):

```bash
docker compose -f docker-compose.prod.yml down -v
docker compose -f docker-compose.prod.yml up --build -d
docker compose -f docker-compose.prod.yml exec api php artisan db:seed --force
```

### Login / session not persisting

- `SANCTUM_STATEFUL_DOMAINS` must include your browser host
- `APP_URL` / `FRONTEND_URL` must match the URL users visit
- HTTPS required in production for secure cookies

### Uploaded images missing after restart (K8s)

Ensure the API pod mounts `qrscan-api-storage` PVC at `/var/www/html/storage`.

### CORS / CSRF errors

The SPA talks to `/api` on the same origin via the web nginx proxy — no separate API subdomain is required.

---

## 7. Local development (unchanged)

```bash
# Database only
docker compose up -d

# Backend
cd backend && php artisan serve

# Frontend
cd frontend && npm run dev
```

Use `docker-compose.prod.yml` only when testing the production container stack locally.
