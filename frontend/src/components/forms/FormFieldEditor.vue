<template>
  <div class="field-editor" :class="{ active: active }" @click.self="$emit('select')">
    <div class="field-editor__toolbar">
      <span class="field-type-badge">{{ typeLabel }}</span>
      <div class="field-editor__actions">
        <button type="button" class="icon-btn" :disabled="index === 0" :title="t('forms.moveUp')" @click.stop="$emit('move-up')">↑</button>
        <button type="button" class="icon-btn" :disabled="index >= total - 1" :title="t('forms.moveDown')" @click.stop="$emit('move-down')">↓</button>
        <button type="button" class="icon-btn" :title="t('forms.duplicate')" @click.stop="$emit('duplicate')">⧉</button>
        <button type="button" class="icon-btn danger" :title="t('common.delete')" @click.stop="$emit('remove')">✕</button>
      </div>
    </div>

    <div v-if="field.type === 'section_header'" class="field-editor__body">
      <input v-model="field.title" class="input-field section-title-input" :placeholder="t('forms.sectionTitle')" />
      <textarea v-model="field.description" class="input-field" rows="2" :placeholder="t('forms.sectionDescription')" />
    </div>

    <div v-else-if="field.type === 'description_text'" class="field-editor__body">
      <input v-model="field.title" class="input-field" :placeholder="t('forms.infoTitleOptional')" />
      <textarea v-model="field.description" class="input-field" rows="3" :placeholder="t('forms.infoText')" />
    </div>

    <div v-else class="field-editor__body">
      <input v-model="field.title" class="input-field question-input" :placeholder="t('forms.question')" />
      <textarea v-model="field.description" class="input-field" rows="2" :placeholder="t('forms.helpText')" />

      <!-- Options editor -->
      <div v-if="hasOptions" class="options-editor">
        <div v-for="(opt, i) in field.options" :key="opt.id" class="option-row">
          <span class="option-marker">{{ field.type === 'checkboxes' ? '☐' : '○' }}</span>
          <input v-model="opt.label" class="input-field" :placeholder="t('forms.option', { n: i + 1 })" />
          <button type="button" class="icon-btn danger" @click="removeOption(i)">✕</button>
        </div>
        <button type="button" class="link-btn" @click="addOption">+ {{ t('forms.addOption') }}</button>
      </div>

      <!-- Grid editor -->
      <div v-if="hasGrid" class="grid-editor">
        <div class="grid-section">
          <span class="mini-label">{{ t('forms.rows') }}</span>
          <div v-for="(row, i) in field.rows" :key="row.id" class="option-row">
            <input v-model="row.label" class="input-field" :placeholder="t('forms.row', { n: i + 1 })" />
            <button type="button" class="icon-btn danger" @click="removeRow(i)">✕</button>
          </div>
          <button type="button" class="link-btn" @click="addRow">+ {{ t('forms.addRow') }}</button>
        </div>
        <div class="grid-section">
          <span class="mini-label">{{ t('forms.columns') }}</span>
          <div v-for="(col, i) in field.columns" :key="col.id" class="option-row">
            <input v-model="col.label" class="input-field" :placeholder="t('forms.column', { n: i + 1 })" />
            <button type="button" class="icon-btn danger" @click="removeColumn(i)">✕</button>
          </div>
          <button type="button" class="link-btn" @click="addColumn">+ {{ t('forms.addColumn') }}</button>
        </div>
      </div>

      <!-- Scale editor -->
      <div v-if="field.type === 'linear_scale'" class="scale-editor">
        <div class="form-row">
          <div class="form-group">
            <label>{{ t('forms.scaleMin') }}</label>
            <input v-model.number="field.scale_min" type="number" min="0" max="10" class="input-field" />
          </div>
          <div class="form-group">
            <label>{{ t('forms.scaleMax') }}</label>
            <input v-model.number="field.scale_max" type="number" min="1" max="10" class="input-field" />
          </div>
        </div>
        <div class="form-row">
          <div class="form-group">
            <label>{{ t('forms.minLabel') }}</label>
            <input v-model="field.scale_min_label" class="input-field" :placeholder="t('forms.notLikely')" />
          </div>
          <div class="form-group">
            <label>{{ t('forms.maxLabel') }}</label>
            <input v-model="field.scale_max_label" class="input-field" :placeholder="t('forms.veryLikely')" />
          </div>
        </div>
      </div>

      <!-- Rating editor -->
      <div v-if="field.type === 'rating'" class="scale-editor">
        <div class="form-group">
          <label>{{ t('forms.ratingMax') }}</label>
          <input v-model.number="field.rating_max" type="number" min="3" max="10" class="input-field" />
        </div>
      </div>

      <label v-if="isInputField(field.type)" class="required-toggle">
        <input v-model="field.required" type="checkbox" />
        {{ t('forms.required') }}
      </label>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { FIELD_TYPES, defaultOption, isInputField } from '../../utils/formFieldTypes'

const props = defineProps({
  field: { type: Object, required: true },
  index: { type: Number, default: 0 },
  total: { type: Number, default: 1 },
  active: { type: Boolean, default: false },
})

defineEmits(['select', 'move-up', 'move-down', 'duplicate', 'remove'])

const { t } = useI18n()

const typeLabel = computed(() => {
  const found = FIELD_TYPES.find(ft => ft.id === props.field.type)
  return found ? t(found.labelKey) : props.field.type
})

const hasOptions = computed(() => ['multiple_choice', 'checkboxes', 'dropdown'].includes(props.field.type))
const hasGrid = computed(() => ['grid_multiple_choice', 'grid_checkbox'].includes(props.field.type))

function addOption() {
  if (!props.field.options) props.field.options = []
  props.field.options.push(defaultOption())
}

function removeOption(i) {
  props.field.options.splice(i, 1)
}

function addRow() {
  if (!props.field.rows) props.field.rows = []
  props.field.rows.push(defaultOption())
}

function removeRow(i) {
  props.field.rows.splice(i, 1)
}

function addColumn() {
  if (!props.field.columns) props.field.columns = []
  props.field.columns.push(defaultOption())
}

function removeColumn(i) {
  props.field.columns.splice(i, 1)
}
</script>

<style scoped>
.field-editor {
  border: 2px solid var(--border);
  border-radius: 0.75rem;
  background: var(--surface);
  transition: border-color 0.15s, box-shadow 0.15s;
  cursor: pointer;
}
.field-editor.active {
  border-color: var(--brand);
  box-shadow: 0 0 0 3px color-mix(in srgb, var(--brand) 15%, transparent);
}
.field-editor__toolbar {
  display: flex; justify-content: space-between; align-items: center;
  padding: 0.5rem 0.75rem; background: var(--bg-subtle);
  border-bottom: 1px solid var(--border); border-radius: 0.625rem 0.625rem 0 0;
}
.field-type-badge { font-size: 0.6875rem; font-weight: 700; text-transform: uppercase; color: var(--text-secondary); }
.field-editor__actions { display: flex; gap: 0.25rem; }
.icon-btn {
  width: 1.75rem; height: 1.75rem; border: none; background: transparent;
  border-radius: 0.375rem; cursor: pointer; font-size: 0.75rem; color: var(--text-secondary);
}
.icon-btn:hover:not(:disabled) { background: var(--border); color: var(--text-primary); }
.icon-btn:disabled { opacity: 0.3; cursor: not-allowed; }
.icon-btn.danger:hover { color: #ef4444; }
.field-editor__body { padding: 1rem; display: flex; flex-direction: column; gap: 0.75rem; }
.question-input { font-size: 1rem; font-weight: 600; }
.section-title-input { font-size: 1.125rem; font-weight: 700; }
.options-editor, .grid-editor { display: flex; flex-direction: column; gap: 0.5rem; }
.option-row { display: flex; align-items: center; gap: 0.5rem; }
.option-marker { color: var(--text-muted); font-size: 0.875rem; }
.link-btn { color: var(--brand); font-weight: 600; background: none; border: none; cursor: pointer; font-size: 0.8125rem; text-align: start; }
.grid-section { display: flex; flex-direction: column; gap: 0.375rem; }
.mini-label { font-size: 0.6875rem; font-weight: 600; text-transform: uppercase; color: var(--text-secondary); }
.form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem; }
.form-group label { display: block; font-size: 0.75rem; font-weight: 600; color: var(--text-secondary); margin-bottom: 0.25rem; }
.required-toggle { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8125rem; color: var(--text-secondary); cursor: pointer; }
</style>
