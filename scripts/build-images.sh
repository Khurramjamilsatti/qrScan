#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
REGISTRY="${REGISTRY:-ghcr.io/khurramjamilsatti}"
TAG="${TAG:-latest}"

cd "$ROOT"

echo "Building ${REGISTRY}/qrscan-api:${TAG}..."
docker build -f backend/Dockerfile -t "${REGISTRY}/qrscan-api:${TAG}" .

echo "Building ${REGISTRY}/qrscan-web:${TAG}..."
docker build -f frontend/Dockerfile -t "${REGISTRY}/qrscan-web:${TAG}" .

echo "Done."
echo "Push with:"
echo "  docker push ${REGISTRY}/qrscan-api:${TAG}"
echo "  docker push ${REGISTRY}/qrscan-web:${TAG}"
