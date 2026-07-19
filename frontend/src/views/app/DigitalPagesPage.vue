<template>
  <div>
    <div class="page-header">
      <div>
        <h2 class="page-title">{{ t('digitalPages.title') }}</h2>
        <p class="page-sub">{{ t('digitalPages.subtitle') }}</p>
      </div>
      <button @click="openCreate" class="btn-primary text-sm">+ {{ t('digitalPages.newPage') }}</button>
    </div>

    <div v-if="editing" class="editor-panel mb-8">
      <div class="editor-panel__header">
        <h3>{{ editId ? t('digitalPages.editPage') : t('digitalPages.createPage') }}</h3>
        <button @click="closeEditor" class="btn-ghost text-sm">✕ {{ t('common.close') }}</button>
      </div>
      <SplitEditor :preview-mode="previewMode">
        <template #form>
          <form @submit.prevent="save" class="editor-form">
            <div class="editor-tabs">
              <button type="button" :class="{ active: editorTab === 'content' }" @click="editorTab = 'content'">{{ t('digitalPages.tabs.content') }}</button>
              <button type="button" :class="{ active: editorTab === 'appearance' }" @click="editorTab = 'appearance'">{{ t('digitalPages.tabs.appearance') }}</button>
              <button type="button" :class="{ active: editorTab === 'qr' }" @click="editorTab = 'qr'">{{ t('digitalPages.tabs.qrDesign') }}</button>
            </div>

            <div class="editor-form__scroll">
            <div v-show="editorTab === 'content'" class="tab-panel">
            <div class="template-section">
              <div class="section-title">{{ t('digitalPages.chooseTemplate') }}</div>
              <TemplateGallery
                :model-value="form.template"
                :templates="templateList"
                :categories="templateCategories"
                :columns="3"
                @update:model-value="selectTemplate"
              />
            </div>
            <DomainSelect v-model="form.custom_domain_id" />
            <div class="form-group">
              <label>{{ t('digitalPages.pageUrlSlug') }}</label>
              <div class="slug-input">
                <span>{{ pageHost }}/page/</span>
                <input v-model="form.slug" required pattern="[a-zA-Z0-9_-]+" class="input-field" placeholder="summer-sale" />
              </div>
            </div>
            <div class="form-group">
              <label>{{ t('digitalPages.pageTitle') }}</label>
              <input v-model="form.title" required class="input-field" placeholder="Summer Sale 2026" />
            </div>

            <!-- Template fields -->
            <div class="content-section">
              <div class="section-title">{{ t('digitalPages.pageContent') }}</div>

              <template v-if="pageLayout === 'landing'">
                <div class="form-group"><label>{{ t('digitalPages.headline') }}</label><input v-model="form.content.headline" class="input-field" /></div>
                <div class="form-group"><label>{{ t('digitalPages.subheadline') }}</label><textarea v-model="form.content.subheadline" class="input-field" rows="2"></textarea></div>
                <div class="form-row">
                  <div class="form-group"><label>{{ t('digitalPages.ctaLabel') }}</label><input v-model="form.content.cta_label" class="input-field" /></div>
                  <div class="form-group"><label>{{ t('digitalPages.ctaUrl') }}</label><input v-model="form.content.cta_url" type="url" class="input-field" /></div>
                </div>
                <div class="repeater-head"><label>{{ t('digitalPages.features') }}</label><button type="button" @click="addFeature" class="text-sm link-btn">+ {{ t('common.add') }}</button></div>
                <div v-for="(f, i) in form.content.features" :key="i" class="repeater-row">
                  <input v-model="f.title" class="input-field" :placeholder="t('common.title')" />
                  <input v-model="f.description" class="input-field" :placeholder="t('common.description')" />
                  <button type="button" @click="form.content.features.splice(i, 1)" class="remove-btn">✕</button>
                </div>
              </template>

              <template v-else-if="pageLayout === 'portfolio'">
                <div class="form-group"><label>{{ t('digitalPages.headline') }}</label><input v-model="form.content.headline" class="input-field" /></div>
                <div class="form-group"><label>{{ t('digitalPages.about') }}</label><textarea v-model="form.content.about" class="input-field" rows="3"></textarea></div>
                <div class="repeater-head"><label>{{ t('digitalPages.projects') }}</label><button type="button" @click="addProject" class="text-sm link-btn">+ {{ t('common.add') }}</button></div>
                <div v-for="(p, i) in form.content.projects" :key="i" class="repeater-block">
                  <input v-model="p.title" class="input-field" :placeholder="t('digitalPages.projectTitle')" />
                  <input v-model="p.description" class="input-field" :placeholder="t('common.description')" />
                  <ImageAssetField v-model="p.image_path" :label="t('digitalPages.projectImage')" folder="photos" ai-context="portfolio" ai-placeholder="project screenshot" />
                  <button type="button" @click="form.content.projects.splice(i, 1)" class="remove-btn block">{{ t('digitalPages.removeProject') }}</button>
                </div>
              </template>

              <template v-else-if="pageLayout === 'event'">
                <div class="form-group"><label>{{ t('digitalPages.eventName') }}</label><input v-model="form.content.event_name" class="input-field" /></div>
                <div class="form-row">
                  <div class="form-group"><label>{{ t('digitalPages.dateTime') }}</label><input v-model="form.content.date" class="input-field" placeholder="June 15, 2026 · 9:00 AM" /></div>
                  <div class="form-group"><label>{{ t('digitalPages.location') }}</label><input v-model="form.content.location" class="input-field" /></div>
                </div>
                <div class="form-group"><label>{{ t('common.description') }}</label><textarea v-model="form.content.description" class="input-field" rows="3"></textarea></div>
                <div class="form-row">
                  <div class="form-group"><label>{{ t('digitalPages.registerLabel') }}</label><input v-model="form.content.cta_label" class="input-field" /></div>
                  <div class="form-group"><label>{{ t('digitalPages.registerUrl') }}</label><input v-model="form.content.cta_url" type="url" class="input-field" /></div>
                </div>
              </template>

              <template v-else-if="pageLayout === 'pricing'">
                <div class="form-group"><label>{{ t('digitalPages.headline') }}</label><input v-model="form.content.headline" class="input-field" /></div>
                <div class="form-group"><label>{{ t('digitalPages.subheadline') }}</label><textarea v-model="form.content.subheadline" class="input-field" rows="2"></textarea></div>
                <div class="repeater-head"><label>{{ t('digitalPages.plans') }}</label><button type="button" @click="addPlan" class="text-sm link-btn">+ {{ t('common.add') }}</button></div>
                <div v-for="(plan, i) in form.content.plans" :key="i" class="repeater-block">
                  <div class="form-row">
                    <input v-model="plan.name" class="input-field" :placeholder="t('digitalPages.planName')" />
                    <input v-model="plan.price" class="input-field" :placeholder="t('digitalPages.planPrice')" />
                  </div>
                  <input v-model="plan.description" class="input-field" :placeholder="t('common.description')" />
                  <textarea v-model="plan.featuresText" class="input-field" rows="2" :placeholder="t('digitalPages.planFeaturesHint')" @input="syncPlanFeatures(plan)"></textarea>
                  <button type="button" @click="form.content.plans.splice(i, 1)" class="remove-btn block">{{ t('common.remove') }}</button>
                </div>
                <div class="form-row">
                  <div class="form-group"><label>{{ t('digitalPages.ctaLabel') }}</label><input v-model="form.content.cta_label" class="input-field" /></div>
                  <div class="form-group"><label>{{ t('digitalPages.ctaUrl') }}</label><input v-model="form.content.cta_url" type="url" class="input-field" /></div>
                </div>
              </template>

              <template v-else-if="pageLayout === 'team'">
                <div class="form-group"><label>{{ t('digitalPages.headline') }}</label><input v-model="form.content.headline" class="input-field" /></div>
                <div class="form-group"><label>{{ t('digitalPages.about') }}</label><textarea v-model="form.content.about" class="input-field" rows="3"></textarea></div>
                <div class="repeater-head"><label>{{ t('digitalPages.teamMembers') }}</label><button type="button" @click="addMember" class="text-sm link-btn">+ {{ t('common.add') }}</button></div>
                <div v-for="(m, i) in form.content.members" :key="i" class="repeater-block">
                  <div class="form-row">
                    <input v-model="m.name" class="input-field" :placeholder="t('common.name')" />
                    <input v-model="m.role" class="input-field" :placeholder="t('digitalPages.jobTitle')" />
                  </div>
                  <textarea v-model="m.bio" class="input-field" rows="2" :placeholder="t('businessCards.bio')" />
                  <ImageAssetField v-model="m.image_path" :label="t('digitalPages.memberPhoto')" folder="photos" variant="compact" />
                  <button type="button" @click="form.content.members.splice(i, 1)" class="remove-btn block">{{ t('common.remove') }}</button>
                </div>
              </template>

              <template v-else-if="pageLayout === 'video'">
                <div class="form-group"><label>{{ t('digitalPages.headline') }}</label><input v-model="form.content.headline" class="input-field" /></div>
                <div class="form-group"><label>{{ t('digitalPages.videoUrl') }}</label><input v-model="form.content.video_url" class="input-field" placeholder="https://www.youtube.com/embed/..." /></div>
                <div class="form-group"><label>{{ t('common.description') }}</label><textarea v-model="form.content.description" class="input-field" rows="3"></textarea></div>
                <div class="form-row">
                  <div class="form-group"><label>{{ t('digitalPages.ctaLabel') }}</label><input v-model="form.content.cta_label" class="input-field" /></div>
                  <div class="form-group"><label>{{ t('digitalPages.ctaUrl') }}</label><input v-model="form.content.cta_url" type="url" class="input-field" /></div>
                </div>
              </template>

              <template v-else-if="pageLayout === 'links'">
                <div class="form-group"><label>{{ t('digitalPages.headline') }}</label><input v-model="form.content.headline" class="input-field" /></div>
                <div class="form-group"><label>{{ t('businessCards.bio') }}</label><textarea v-model="form.content.bio" class="input-field" rows="2"></textarea></div>
                <div class="repeater-head"><label>{{ t('common.links') }}</label><button type="button" @click="addProfileLink" class="text-sm link-btn">+ {{ t('digitalPages.addLink') }}</button></div>
                <div v-for="(link, i) in form.content.profile_links" :key="i" class="repeater-row">
                  <input v-model="link.label" class="input-field" :placeholder="t('common.label')" />
                  <input v-model="link.url" class="input-field" placeholder="https://..." />
                  <button type="button" @click="form.content.profile_links.splice(i, 1)" class="remove-btn">✕</button>
                </div>
              </template>

              <template v-else-if="pageLayout === 'resume'">
                <div class="form-group"><label>{{ t('common.name') }}</label><input v-model="form.content.headline" class="input-field" /></div>
                <div class="form-group"><label>{{ t('digitalPages.summary') }}</label><textarea v-model="form.content.about" class="input-field" rows="3"></textarea></div>
                <div class="form-group">
                  <label>{{ t('digitalPages.skills') }}</label>
                  <input v-model="form.content.skillsText" class="input-field" :placeholder="t('digitalPages.skillsHint')" @input="syncSkills" />
                </div>
                <div class="repeater-head"><label>{{ t('digitalPages.experience') }}</label><button type="button" @click="addExperience" class="text-sm link-btn">+ {{ t('common.add') }}</button></div>
                <div v-for="(exp, i) in form.content.experience" :key="i" class="repeater-block">
                  <div class="form-row">
                    <input v-model="exp.title" class="input-field" :placeholder="t('digitalPages.jobTitle')" />
                    <input v-model="exp.company" class="input-field" :placeholder="t('businessCards.company')" />
                  </div>
                  <input v-model="exp.period" class="input-field" :placeholder="t('digitalPages.period')" />
                  <textarea v-model="exp.description" class="input-field" rows="2" :placeholder="t('common.description')" />
                  <button type="button" @click="form.content.experience.splice(i, 1)" class="remove-btn block">{{ t('common.remove') }}</button>
                </div>
                <div class="form-row">
                  <div class="form-group"><label>{{ t('digitalPages.ctaLabel') }}</label><input v-model="form.content.cta_label" class="input-field" /></div>
                  <div class="form-group"><label>{{ t('digitalPages.ctaUrl') }}</label><input v-model="form.content.cta_url" type="url" class="input-field" /></div>
                </div>
              </template>

              <template v-else>
                <div class="form-group"><label>{{ t('digitalPages.headline') }}</label><input v-model="form.content.headline" class="input-field" /></div>
                <div class="form-group"><label>{{ t('digitalPages.body') }}</label><textarea v-model="form.content.body" class="input-field" rows="5"></textarea></div>
                <div class="form-row">
                  <div class="form-group"><label>{{ t('digitalPages.buttonLabel') }}</label><input v-model="form.content.cta_label" class="input-field" /></div>
                  <div class="form-group"><label>{{ t('digitalPages.buttonUrl') }}</label><input v-model="form.content.cta_url" type="url" class="input-field" /></div>
                </div>
              </template>
            </div>

            <!-- Page features: sort + show/hide -->
            <div class="features-panel">
              <div class="section-title">{{ t('digitalPages.pageFeatures') }}</div>
              <p class="field-hint">{{ t('digitalPages.pageFeaturesHint') }}</p>

              <div
                v-for="(sectionId, idx) in form.content.sections_order"
                :key="sectionId"
                class="feature-block"
                :class="{ 'feature-block--on': sectionEnabled(sectionId), 'feature-block--open': expandedSections[sectionId] }"
              >
                <div class="feature-block__head">
                  <button type="button" class="feature-block__toggle" @click="toggleSectionExpand(sectionId)">
                    <span class="feature-block__icon">{{ sectionMeta(sectionId).icon }}</span>
                    <span class="feature-block__label">{{ sectionMeta(sectionId).label }}</span>
                    <span class="feature-block__chevron">{{ expandedSections[sectionId] ? '▾' : '▸' }}</span>
                  </button>
                  <div class="feature-block__actions">
                    <button type="button" class="sort-btn" :title="t('common.moveUp')" :disabled="idx === 0" @click="moveSection(idx, -1)">↑</button>
                    <button type="button" class="sort-btn" :title="t('common.moveDown')" :disabled="idx === form.content.sections_order.length - 1" @click="moveSection(idx, 1)">↓</button>
                    <label class="show-toggle" :title="sectionEnabled(sectionId) ? t('common.visibleOnPage') : t('common.hiddenOnPage')">
                      <input
                        type="checkbox"
                        :checked="sectionEnabled(sectionId)"
                        @change="setSectionEnabled(sectionId, $event.target.checked)"
                      />
                      <span class="show-toggle__track"></span>
                    </label>
                  </div>
                </div>

                <div v-if="expandedSections[sectionId]" class="feature-block__body">
                  <!-- Gallery -->
                  <template v-if="sectionId === 'gallery'">
                    <div class="form-row">
                      <div class="form-group"><label>{{ t('digitalPages.sectionTitle') }}</label><input v-model="form.content.gallery.title" class="input-field" /></div>
                      <div class="form-group">
                        <label>{{ t('digitalPages.gridLayout') }}</label>
                        <select v-model="form.content.gallery.layout" class="input-field">
                          <option v-for="l in galleryLayouts" :key="l.id" :value="l.id">{{ l.label }}</option>
                        </select>
                      </div>
                    </div>
                    <GalleryImagesField
                      :model-value="form.content.gallery.items"
                      :label="t('digitalPages.photos')"
                      @update:model-value="onGalleryItemsChange"
                    />
                  </template>

                  <!-- Calendar -->
                  <template v-else-if="sectionId === 'calendar'">
                    <div class="form-group"><label>{{ t('digitalPages.sectionTitle') }}</label><input v-model="form.content.calendar.title" class="input-field" /></div>
                    <div class="form-group">
                      <label>{{ t('digitalPages.googleCalendarEmbedUrl') }}</label>
                      <input v-model="form.content.calendar.embed_url" class="input-field" placeholder="https://calendar.google.com/calendar/embed?src=..." />
                      <p class="field-hint">{{ t('digitalPages.googleCalendarHint') }}</p>
                    </div>
                    <div class="repeater-head"><label>{{ t('digitalPages.manualEvents') }}</label><button type="button" @click="addCalendarEvent" class="link-btn">+ {{ t('digitalPages.addEvent') }}</button></div>
                    <div v-for="(ev, i) in form.content.calendar.events" :key="i" class="repeater-block">
                      <input v-model="ev.title" class="input-field" :placeholder="t('digitalPages.eventTitle')" />
                      <div class="form-row">
                        <input v-model="ev.date" class="input-field" placeholder="Date (e.g. 2026-06-15)" />
                        <input v-model="ev.time" class="input-field" :placeholder="t('digitalPages.timeOptional')" />
                      </div>
                      <input v-model="ev.location" class="input-field" :placeholder="t('digitalPages.location')" />
                      <input v-model="ev.description" class="input-field" :placeholder="t('common.description')" />
                      <input v-model="ev.url" class="input-field" :placeholder="t('digitalPages.detailsUrlOptional')" />
                      <button type="button" @click="form.content.calendar.events.splice(i, 1)" class="remove-btn block">{{ t('digitalPages.removeEvent') }}</button>
                    </div>
                  </template>

                  <!-- Contact -->
                  <template v-else-if="sectionId === 'contact'">
                    <div class="form-group"><label>{{ t('common.name') }}</label><input v-model="form.content.contact.name" class="input-field" /></div>
                    <div class="form-row">
                      <div class="form-group"><label>{{ t('common.email') }}</label><input v-model="form.content.contact.email" type="email" class="input-field" /></div>
                      <div class="form-group"><label>{{ t('common.phone') }}</label><input v-model="form.content.contact.phone" class="input-field" /></div>
                    </div>
                    <div class="form-group"><label>{{ t('common.address') }}</label><input v-model="form.content.contact.address" class="input-field" /></div>
                    <div class="form-group"><label>{{ t('common.website') }}</label><input v-model="form.content.contact.website" type="url" class="input-field" /></div>
                  </template>

                  <!-- Social -->
                  <template v-else-if="sectionId === 'social'">
                    <div class="form-group"><label>{{ t('digitalPages.sectionTitle') }}</label><input v-model="form.content.social.title" class="input-field" /></div>
                    <div class="repeater-head"><label>{{ t('digitalPages.profiles') }}</label><button type="button" @click="addSocial" class="link-btn">+ {{ t('common.add') }}</button></div>
                    <div v-for="(s, i) in form.content.social.links" :key="i" class="social-row">
                      <select v-model="s.platform" class="input-field">
                        <option v-for="p in socialPlatforms" :key="p.id" :value="p.id">{{ p.label }}</option>
                      </select>
                      <input v-model="s.url" class="input-field" placeholder="https://..." />
                      <button type="button" @click="form.content.social.links.splice(i, 1)" class="remove-btn">✕</button>
                    </div>
                  </template>

                  <!-- Extra links -->
                  <template v-else-if="sectionId === 'extra_links'">
                    <div class="form-group"><label>{{ t('digitalPages.sectionTitle') }}</label><input v-model="form.content.extra_links.title" class="input-field" :placeholder="t('common.links')" /></div>
                    <div class="form-group">
                      <label>{{ t('digitalPages.quickLinkUrl') }}</label>
                      <input v-model="form.content.extra_links.url" type="url" class="input-field" placeholder="https://..." />
                      <p class="field-hint">{{ t('digitalPages.quickLinkHint') }}</p>
                    </div>
                    <div class="repeater-head"><label>{{ t('digitalPages.moreLinks') }}</label><button type="button" @click="addExtraLink" class="link-btn">+ {{ t('digitalPages.addLink') }}</button></div>
                    <div v-for="(link, i) in form.content.extra_links.items" :key="i" class="repeater-block">
                      <div class="form-row">
                        <div class="form-group"><label>{{ t('common.label') }}</label><input v-model="link.label" class="input-field" placeholder="Menu PDF" /></div>
                        <div class="form-group">
                          <label>{{ t('digitalPages.icon') }}</label>
                          <select v-model="link.icon" class="input-field">
                            <option v-for="ic in extraLinkIcons" :key="ic.id" :value="ic.id">{{ ic.label }}</option>
                          </select>
                        </div>
                      </div>
                      <input v-model="link.url" class="input-field" placeholder="https://..." />
                      <button type="button" @click="form.content.extra_links.items.splice(i, 1)" class="remove-btn block">{{ t('digitalPages.removeLink') }}</button>
                    </div>
                  </template>
                </div>
              </div>
            </div>
            </div>

            <div v-show="editorTab === 'appearance'" class="tab-panel">
              <ImageAssetField v-model="form.logo_path" :label="t('common.logo')" folder="logos" ai-context="qr-logo" ai-placeholder="minimal brand logo" />
              <ImageAssetField v-model="form.background_image_path" :label="t('businessCards.headerBackground')" folder="backgrounds" ai-context="page-background" ai-placeholder="abstract gradient header" />
              <div class="form-group">
                <label>{{ t('common.themeColor') }}</label>
                <input v-model="form.theme_color" type="color" class="color-input" />
              </div>
            </div>

            <div v-show="editorTab === 'qr'" class="tab-panel">
            <div class="qr-section">
              <div class="section-title">{{ t('digitalPages.pageQrStyle') }}</div>
              <QrStyleFields
                v-model:qr-shape="form.qr_shape"
                v-model:dot-style="form.dot_style"
                v-model:corner-style="form.corner_style"
                v-model:frame-style="form.frame_style"
              />
            </div>
            </div>

            <p v-if="error" class="error-text">{{ error }}</p>
            </div>

            <div class="form-actions">
              <button type="button" @click="closeEditor" class="btn-secondary">{{ t('common.cancel') }}</button>
              <button type="submit" :disabled="saving" class="btn-primary">{{ saving ? t('common.saving') : (editId ? t('common.update') : t('common.create')) }}</button>
            </div>
          </form>
        </template>
        <template #preview>
          <div v-if="editorTab === 'qr'" class="preview-stage preview-stage--qr">
            <QrPreview
              minimal
              :content="previewPageUrl"
              :name="form.title || t('digitalPages.newPage')"
              :foreground="form.theme_color"
              :logo-url="form.logo_path"
              :background-image="form.background_image_path"
              :background="'#ffffff'"
              :size="240"
              :qr-shape="form.qr_shape"
              :dot-style="form.dot_style"
              :corner-style="form.corner_style"
              :frame-style="form.frame_style"
            />
            <p class="preview-qr-url">{{ previewPageUrl }}</p>
          </div>
          <PagePreview
            v-else
            :title="form.title"
            :template="form.template"
            :content="previewContent({ template: form.template, content: form.content })"
            :theme-color="form.theme_color"
            :logo="form.logo_path"
            :background-image="form.background_image_path"
            :page-url="previewPageUrl"
            :domain-label="domains.labelFor(form.custom_domain_id)"
          />
        </template>
      </SplitEditor>
    </div>

    <template v-if="!editing">
      <div v-if="loading" class="text-muted">{{ t('common.loading') }}</div>
      <div v-else-if="!pages.length" class="empty-state">
        <div class="empty-icon">📄</div>
        <h3>{{ t('digitalPages.emptyTitle') }}</h3>
        <p>{{ t('digitalPages.emptyDesc') }}</p>
        <button @click="openCreate" class="btn-primary">{{ t('digitalPages.emptyCta') }}</button>
      </div>
      <div v-else class="pages-grid">
        <div v-for="page in pages" :key="page.id" class="page-item" :class="{ draft: !page.is_active }">
          <div class="page-item__stack">
            <div v-if="!page.is_active" class="draft-ribbon">{{ t('publish.draft') }}</div>
            <PageHtmlPreview
              compact
              embedded
              :title="page.title"
              :template="page.template"
              :content="previewContent(page)"
              :theme-color="page.theme_color"
              :logo="page.logo_path"
              :background-image="page.background_image_path"
              :page-url="page.page_url"
              :domain-label="page.domain_label"
            />
            <div class="page-item__footer">
              <PublishToggle
                :model-value="!!page.is_active"
                :loading="togglingId === page.id"
                :active-label="t('publish.published')"
                :inactive-label="t('publish.draft')"
                @update:model-value="togglePublish(page)"
              />
              <span class="view-stat">
                <span class="view-stat__num">{{ page.view_count }}</span>
                <span class="view-stat__label">{{ t('common.views') }}</span>
              </span>
              <div class="page-item__actions">
                <CopyButton :text="page.page_url" :label="t('common.copy')" />
                <button @click="openEdit(page)" class="action-btn">{{ t('common.edit') }}</button>
                <button @click="showAnalytics(page)" class="action-btn">{{ t('common.stats') }}</button>
                <button @click="deletePage(page)" class="action-btn danger">{{ t('common.delete') }}</button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <p v-if="loadError" class="error-text mt-4">{{ loadError }}</p>
    </template>

    <div v-if="analyticsPage" class="drawer-overlay" @click.self="analyticsPage = null">
      <div class="drawer">
        <div class="drawer-header">
          <h3>{{ t('common.analyticsTitle', { name: analyticsPage.title }) }}</h3>
          <button @click="analyticsPage = null" class="btn-ghost">✕</button>
        </div>
        <AnalyticsPanel type="digital-pages" :id="analyticsPage.id" />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import api from '../../services/api'
import { useDomainsStore } from '../../stores/domains'
import SplitEditor from '../../components/ui/SplitEditor.vue'
import PageHtmlPreview from '../../components/previews/PageHtmlPreview.vue'
import PagePreview from '../../components/previews/PagePreview.vue'
import QrPreview from '../../components/previews/QrPreview.vue'
import CopyButton from '../../components/ui/CopyButton.vue'
import AnalyticsPanel from '../../components/ui/AnalyticsPanel.vue'
import DomainSelect from '../../components/ui/DomainSelect.vue'
import ImageAssetField from '../../components/ui/ImageAssetField.vue'
import GalleryImagesField from '../../components/ui/GalleryImagesField.vue'
import PublishToggle from '../../components/ui/PublishToggle.vue'
import QrStyleFields from '../../components/ui/QrStyleFields.vue'
import TemplateGallery from '../../components/ui/TemplateGallery.vue'
import { useDialog } from '../../composables/useDialog'
import { translateList } from '../../composables/useTranslatedOptions.js'
import {
  TEMPLATE_LIST,
  PAGE_TEMPLATE_CATEGORIES,
  defaultContentForTemplate,
  mergePageContent,
  pickPageExtras,
  getPageTemplateLayout,
} from '../../utils/pageTemplates'
import { PAGE_SECTION_MAP, isSectionEnabled as checkSectionEnabled, sectionLabel } from '../../utils/pageSections'
import { GALLERY_LAYOUTS, EXTRA_LINK_ICONS, SOCIAL_PLATFORMS } from '../../utils/socialPlatforms'

const { t } = useI18n()
const domains = useDomainsStore()
const dialog = useDialog()

const templateList = computed(() => translateList(TEMPLATE_LIST, t))
const templateCategories = computed(() => translateList(PAGE_TEMPLATE_CATEGORIES, t))
const pageLayout = computed(() => getPageTemplateLayout(form.value.template))
const galleryLayouts = computed(() => translateList(GALLERY_LAYOUTS, t))
const socialPlatforms = computed(() => translateList(SOCIAL_PLATFORMS, t))
const extraLinkIcons = computed(() => translateList(EXTRA_LINK_ICONS, t))

const pages = ref([])
const loading = ref(true)
const editing = ref(false)
const editId = ref(null)
const saving = ref(false)
const error = ref('')
const analyticsPage = ref(null)
const togglingId = ref(null)
const expandedSections = ref({})
const loadError = ref('')
const editorTab = ref('content')

const pageHost = computed(() => {
  try { return new URL(domains.baseUrlFor(form.value?.custom_domain_id)).host } catch { return 'localhost' }
})

const defaultForm = () => ({
  slug: '', title: '', template: 'landing',
  content: defaultContentForTemplate('landing'),
  theme_color: '#e8655a', logo_path: '', background_image_path: '',
  qr_shape: 'square', dot_style: 'square', corner_style: 'sharp', frame_style: 'none',
  custom_domain_id: null,
})
const form = ref(defaultForm())

const previewPageUrl = computed(() => {
  const base = domains.baseUrlFor(form.value.custom_domain_id)
  return form.value.slug ? `${base}/page/${form.value.slug}` : `${base}/page/preview`
})
const previewMode = computed(() => (editorTab.value === 'qr' ? 'qr' : 'content'))

function previewContent(page) {
  return mergePageContent(page.template, page.content || {})
}

function sectionMeta(id) {
  const section = PAGE_SECTION_MAP[id]
  if (!section) return { label: id, icon: '•' }
  return { label: sectionLabel(id, t), icon: section.icon }
}

function sectionEnabled(id) {
  return checkSectionEnabled(id, form.value.content)
}

function setSectionEnabled(id, on) {
  const block = sectionBlock(id)
  if (block) block.enabled = on
  if (on) expandedSections.value[id] = true
}

function sectionBlock(id) {
  if (id === 'social') return form.value.content.social
  if (id === 'extra_links') return form.value.content.extra_links
  return form.value.content[id]
}

function toggleSectionExpand(id) {
  expandedSections.value[id] = !expandedSections.value[id]
}

function moveSection(idx, delta) {
  const order = form.value.content.sections_order
  const next = idx + delta
  if (next < 0 || next >= order.length) return
  const copy = [...order]
  ;[copy[idx], copy[next]] = [copy[next], copy[idx]]
  form.value.content.sections_order = copy
}

function selectTemplate(id) {
  if (form.value.template === id) return
  const extras = pickPageExtras(form.value.content)
  form.value.template = id
  form.value.content = mergePageContent(id, extras)
}

function addFeature() {
  if (!form.value.content.features) form.value.content.features = []
  form.value.content.features.push({ title: '', description: '' })
}

function addProject() {
  if (!form.value.content.projects) form.value.content.projects = []
  form.value.content.projects.push({ title: '', description: '', image_path: '' })
}

function addSocial() {
  if (!form.value.content.social.links) form.value.content.social.links = []
  form.value.content.social.links.push({ platform: 'linkedin', url: '' })
  form.value.content.social.enabled = true
  expandedSections.value.social = true
}

function addExtraLink() {
  if (!form.value.content.extra_links.items) form.value.content.extra_links.items = []
  form.value.content.extra_links.items.push({ label: '', url: '', icon: 'link' })
  form.value.content.extra_links.enabled = true
  expandedSections.value.extra_links = true
}

function onGalleryItemsChange(items) {
  form.value.content.gallery.items = items
  if (items?.length) {
    form.value.content.gallery.enabled = true
    expandedSections.value.gallery = true
  }
}

function addGalleryItem() {
  if (!form.value.content.gallery.items) form.value.content.gallery.items = []
  form.value.content.gallery.items.push({ image_path: '', caption: '', url: '' })
  form.value.content.gallery.enabled = true
  expandedSections.value.gallery = true
}

function addPlan() {
  if (!form.value.content.plans) form.value.content.plans = []
  form.value.content.plans.push({ name: '', price: '', description: '', features: [], featuresText: '' })
}

function syncPlanFeatures(plan) {
  plan.features = (plan.featuresText || '').split('\n').map((s) => s.trim()).filter(Boolean)
}

function addMember() {
  if (!form.value.content.members) form.value.content.members = []
  form.value.content.members.push({ name: '', role: '', bio: '', image_path: '' })
}

function addProfileLink() {
  if (!form.value.content.profile_links) form.value.content.profile_links = []
  form.value.content.profile_links.push({ label: '', url: '' })
}

function addExperience() {
  if (!form.value.content.experience) form.value.content.experience = []
  form.value.content.experience.push({ title: '', company: '', period: '', description: '' })
}

function syncSkills() {
  form.value.content.skills = (form.value.content.skillsText || '').split(',').map((s) => s.trim()).filter(Boolean)
}

function addCalendarEvent() {
  if (!form.value.content.calendar.events) form.value.content.calendar.events = []
  form.value.content.calendar.events.push({ title: '', date: '', time: '', location: '', description: '', url: '' })
  form.value.content.calendar.enabled = true
  expandedSections.value.calendar = true
}

function openCreate() {
  editId.value = null
  form.value = defaultForm()
  expandedSections.value = { contact: true }
  editorTab.value = 'content'
  editing.value = true
  error.value = ''
}

function openEdit(page) {
  editId.value = page.id
  const content = mergePageContent(page.template, page.content || {})
  if (content.plans) {
    content.plans = content.plans.map((p) => ({
      ...p,
      featuresText: (p.features || []).join('\n'),
    }))
  }
  if (content.skills) {
    content.skillsText = content.skills.join(', ')
  }
  form.value = {
    ...page,
    content,
    theme_color: page.theme_color || '#e8655a',
    qr_shape: page.qr_shape || 'square',
    dot_style: page.dot_style || 'square',
    corner_style: page.corner_style || 'sharp',
    frame_style: page.frame_style || 'none',
  }
  editorTab.value = 'content'
  editing.value = true
  error.value = ''
  expandedSections.value = Object.fromEntries(
    (form.value.content.sections_order || []).map((id) => [id, sectionEnabled(id)])
  )
}

function closeEditor() {
  editing.value = false
  editId.value = null
}

async function load() {
  loadError.value = ''
  try {
    const { data } = await api.get('/digital-pages')
    pages.value = Array.isArray(data) ? data : []
  } catch (e) {
    pages.value = []
    loadError.value = e.response?.data?.message || t('digitalPages.loadFailed')
  }
}

async function save() {
  saving.value = true
  error.value = ''
  try {
    const content = JSON.parse(JSON.stringify(form.value.content))
    if (content.plans) {
      content.plans = content.plans.map(({ featuresText, ...plan }) => ({
        ...plan,
        features: plan.features || (featuresText || '').split('\n').map((s) => s.trim()).filter(Boolean),
      }))
    }
    if (content.skillsText) {
      content.skills = content.skillsText.split(',').map((s) => s.trim()).filter(Boolean)
      delete content.skillsText
    }
    const payload = {
      ...form.value,
      content,
    }
    delete payload.is_active
    delete payload.page_url
    delete payload.domain_label
    delete payload.view_count
    if (editId.value) await api.put(`/digital-pages/${editId.value}`, payload)
    else await api.post('/digital-pages', { ...payload, is_active: false })
    closeEditor()
    await load()
  } catch (e) {
    error.value = e.response?.data?.message || Object.values(e.response?.data?.errors || {}).flat().join(', ') || t('errors.failedToSave')
  } finally {
    saving.value = false
  }
}

async function togglePublish(page) {
  togglingId.value = page.id
  try {
    const { data } = await api.patch(`/digital-pages/${page.id}/publish`)
    const idx = pages.value.findIndex(p => p.id === page.id)
    if (idx !== -1) pages.value[idx] = data
  } catch {
    dialog.alert({
      title: t('common.notice'),
      message: t('digitalPages.updateFailedMessage'),
      variant: 'danger',
    })
  } finally {
    togglingId.value = null
  }
}

async function deletePage(page) {
  const ok = await dialog.confirm({
    title: t('digitalPages.deleteTitle'),
    message: t('digitalPages.deleteMessage', { title: page.title }),
    confirmText: t('common.delete'),
    variant: 'danger',
  })
  if (!ok) return
  await api.delete(`/digital-pages/${page.id}`)
  await load()
}

function showAnalytics(page) { analyticsPage.value = page }

onMounted(async () => {
  domains.fetch()
  try { await load() } finally { loading.value = false }
})
</script>

<style scoped>
.page-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 1.5rem; }
.page-title { font-size: 1.5rem; font-weight: 700; color: var(--text-primary); }
.page-sub { color: var(--text-secondary); font-size: 0.875rem; margin-top: 0.25rem; }
.editor-panel { background: var(--surface); border: 1px solid var(--border); border-radius: 1.25rem; padding: 1.5rem; box-shadow: var(--shadow-sm); }
.editor-panel__header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.editor-panel__header h3 { font-weight: 700; font-size: 1.125rem; color: var(--text-primary); }
.form-group label { display: block; font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.375rem; }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
.slug-input { display: flex; align-items: center; gap: 0.5rem; }
.slug-input span { font-size: 0.8125rem; color: var(--text-muted); white-space: nowrap; }
.color-input { width: 100%; height: 2.5rem; border-radius: 0.5rem; cursor: pointer; border: 1px solid var(--border); }
.template-section, .content-section, .qr-section, .features-panel {
  background: var(--bg-subtle); border: 1px solid var(--border); border-radius: 0.75rem; padding: 1rem;
  display: flex; flex-direction: column; gap: 0.75rem;
}
.features-panel { gap: 0.625rem; padding-bottom: 0.25rem; margin-bottom: 0.25rem; }
.feature-block {
  border: 1px solid var(--border);
  border-radius: 0.75rem;
  background: var(--surface);
  overflow: hidden;
  transition: border-color 0.15s;
}
.feature-block--on { border-color: color-mix(in srgb, var(--brand) 35%, var(--border)); }
.feature-block__head {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 0.5rem;
  padding: 0.5rem 0.625rem;
  background: var(--bg-subtle);
}
.feature-block__toggle {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  flex: 1;
  min-width: 0;
  border: none;
  background: none;
  cursor: pointer;
  text-align: left;
  padding: 0.25rem;
  color: var(--text-primary);
}
.feature-block__icon { font-size: 1rem; line-height: 1; }
.feature-block__label { font-size: 0.8125rem; font-weight: 700; }
.feature-block__chevron { font-size: 0.625rem; color: var(--text-muted); margin-left: auto; }
.feature-block__actions { display: flex; align-items: center; gap: 0.25rem; flex-shrink: 0; }
.sort-btn {
  width: 1.75rem;
  height: 1.75rem;
  border-radius: 0.375rem;
  border: 1px solid var(--border);
  background: var(--surface);
  color: var(--text-secondary);
  font-size: 0.75rem;
  cursor: pointer;
  line-height: 1;
}
.sort-btn:hover:not(:disabled) { border-color: var(--brand); color: var(--brand); }
.sort-btn:disabled { opacity: 0.35; cursor: not-allowed; }
.show-toggle { position: relative; display: inline-flex; cursor: pointer; }
.show-toggle input { position: absolute; opacity: 0; width: 0; height: 0; }
.show-toggle__track {
  width: 2.25rem;
  height: 1.25rem;
  border-radius: 9999px;
  background: var(--border);
  position: relative;
  transition: background 0.15s;
}
.show-toggle__track::after {
  content: '';
  position: absolute;
  top: 0.15rem;
  left: 0.15rem;
  width: 0.95rem;
  height: 0.95rem;
  border-radius: 50%;
  background: #fff;
  transition: transform 0.15s;
  box-shadow: 0 1px 3px rgba(0,0,0,0.15);
}
.show-toggle input:checked + .show-toggle__track { background: var(--brand); }
.show-toggle input:checked + .show-toggle__track::after { transform: translateX(1rem); }
.feature-block__body {
  padding: 0.875rem;
  border-top: 1px solid var(--border);
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.field-hint { font-size: 0.6875rem; color: var(--text-muted); margin-top: 0.25rem; }
.social-row { display: grid; grid-template-columns: 8rem 1fr auto; gap: 0.5rem; margin-bottom: 0.5rem; }
@media (max-width: 640px) { .social-row { grid-template-columns: 1fr; } }
.section-title { font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); text-transform: uppercase; letter-spacing: 0.05em; }
.repeater-head { display: flex; justify-content: space-between; align-items: center; }
.repeater-head label { font-size: 0.8125rem; font-weight: 600; color: var(--text-secondary); }
.link-btn { color: var(--brand); font-weight: 600; background: none; border: none; cursor: pointer; }
.repeater-row { display: grid; grid-template-columns: 1fr 1fr auto; gap: 0.5rem; margin-bottom: 0.5rem; }
.repeater-block { display: flex; flex-direction: column; gap: 0.5rem; padding: 0.75rem; background: var(--surface); border-radius: 0.5rem; border: 1px solid var(--border); margin-bottom: 0.5rem; }
.remove-btn { color: #ef4444; background: none; border: none; cursor: pointer; font-size: 0.875rem; }
.remove-btn.block { font-size: 0.75rem; text-align: left; padding: 0; }
.form-actions { display: flex; gap: 0.75rem; padding-top: 0.5rem; }
.error-text { color: #ef4444; font-size: 0.875rem; }
.pages-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 1rem; align-items: start; }
.page-item {
  display: flex;
  justify-content: center;
}
.page-item__stack {
  position: relative;
  width: 100%;
  max-width: 400px;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}
.page-item.draft { opacity: 0.88; }
.draft-ribbon {
  position: absolute; top: 0.5rem; left: 0.5rem; z-index: 5;
  font-size: 0.5625rem; font-weight: 700; text-transform: uppercase;
  padding: 0.15rem 0.4rem; border-radius: 0.25rem;
  background: var(--gold-muted); color: #92680a;
}
.page-item__footer {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  gap: 0.5rem;
  width: 100%;
}
.view-stat {
  display: inline-flex; align-items: baseline; gap: 0.2rem;
  padding: 0.15rem 0.5rem; border-radius: 9999px;
  background: var(--purple-muted); border: 1px solid color-mix(in srgb, var(--purple) 25%, var(--border));
}
.view-stat__num { font-size: 0.75rem; font-weight: 700; color: var(--purple); }
.view-stat__label { font-size: 0.625rem; font-weight: 600; text-transform: uppercase; }
.page-item__actions { display: flex; flex-wrap: wrap; gap: 0.375rem; margin-left: auto; }
.action-btn {
  font-size: 0.75rem; font-weight: 500; padding: 0.25rem 0.5rem;
  border-radius: 0.375rem; border: 1px solid var(--border); background: var(--bg-subtle);
  color: var(--text-secondary); cursor: pointer;
}
.action-btn:hover { border-color: var(--brand); color: var(--brand); }
.action-btn.danger:hover { border-color: #ef4444; color: #ef4444; }
.drawer-overlay { position: fixed; inset: 0; background: rgba(26,19,51,0.45); z-index: 50; display: flex; justify-content: flex-end; }
.drawer { width: 100%; max-width: 420px; background: var(--surface); height: 100%; padding: 1.5rem; overflow-y: auto; border-left: 1px solid var(--border); }
.drawer-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; }
.drawer-header h3 { color: var(--text-primary); font-weight: 700; }
.preview-stage--qr {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 1rem;
  width: 100%;
  min-height: 320px;
  padding: 1.5rem;
  border-radius: 1.25rem;
  background: linear-gradient(180deg, var(--bg-subtle), var(--surface));
  border: 1px solid var(--border);
}
.preview-qr-url {
  font-size: 0.6875rem;
  font-family: ui-monospace, monospace;
  color: var(--text-muted);
  text-align: center;
  word-break: break-all;
  max-width: 260px;
}
</style>
