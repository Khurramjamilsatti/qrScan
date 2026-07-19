<template>
  <div class="form-field" :class="[`form-field--${field.type}`, variantClass, { 'form-field--error': error }]">
    <!-- Section header -->
    <div v-if="field.type === 'section_header'" class="section-header">
      <h3 class="section-header__title">{{ field.title || t('forms.untitledSection') }}</h3>
      <p v-if="field.description" class="section-header__desc">{{ field.description }}</p>
    </div>

    <!-- Description text -->
    <div v-else-if="field.type === 'description_text'" class="description-block">
      <p class="description-block__title" v-if="field.title">{{ field.title }}</p>
      <p class="description-block__text">{{ field.description || t('forms.emptyDescription') }}</p>
    </div>

    <!-- Input fields -->
    <template v-else>
      <label class="field-label">
        {{ field.title || t('forms.untitledQuestion') }}
        <span v-if="field.required" class="required-mark">*</span>
      </label>
      <p v-if="field.description" class="field-desc">{{ field.description }}</p>

      <!-- Short text -->
      <input
        v-if="field.type === 'short_text'"
        :value="modelValue"
        type="text"
        class="input-field"
        :placeholder="t('forms.shortAnswer')"
        :disabled="disabled"
        @input="$emit('update:modelValue', $event.target.value)"
      />

      <!-- Paragraph -->
      <textarea
        v-else-if="field.type === 'paragraph'"
        :value="modelValue"
        class="input-field"
        rows="4"
        :placeholder="t('forms.longAnswer')"
        :disabled="disabled"
        @input="$emit('update:modelValue', $event.target.value)"
      />

      <!-- Email -->
      <input
        v-else-if="field.type === 'email'"
        :value="modelValue"
        type="email"
        class="input-field"
        :placeholder="t('forms.emailPlaceholder')"
        :disabled="disabled"
        @input="$emit('update:modelValue', $event.target.value)"
      />

      <!-- Number -->
      <input
        v-else-if="field.type === 'number'"
        :value="modelValue"
        type="number"
        class="input-field"
        :disabled="disabled"
        @input="$emit('update:modelValue', $event.target.value)"
      />

      <!-- URL -->
      <input
        v-else-if="field.type === 'url'"
        :value="modelValue"
        type="url"
        class="input-field"
        placeholder="https://"
        :disabled="disabled"
        @input="$emit('update:modelValue', $event.target.value)"
      />

      <!-- Date -->
      <input
        v-else-if="field.type === 'date'"
        :value="modelValue"
        type="date"
        class="input-field"
        :disabled="disabled"
        @input="$emit('update:modelValue', $event.target.value)"
      />

      <!-- Time -->
      <input
        v-else-if="field.type === 'time'"
        :value="modelValue"
        type="time"
        class="input-field"
        :disabled="disabled"
        @input="$emit('update:modelValue', $event.target.value)"
      />

      <!-- Multiple choice -->
      <div v-else-if="field.type === 'multiple_choice'" class="choice-list">
        <label v-for="opt in field.options || []" :key="opt.id" class="choice-item">
          <input
            type="radio"
            :name="`field-${field.id}`"
            :value="opt.label"
            :checked="modelValue === opt.label"
            :disabled="disabled"
            @change="$emit('update:modelValue', opt.label)"
          />
          <span>{{ opt.label }}</span>
        </label>
      </div>

      <!-- Checkboxes -->
      <div v-else-if="field.type === 'checkboxes'" class="choice-list">
        <label v-for="opt in field.options || []" :key="opt.id" class="choice-item">
          <input
            type="checkbox"
            :value="opt.label"
            :checked="(modelValue || []).includes(opt.label)"
            :disabled="disabled"
            @change="toggleCheckbox(opt.label)"
          />
          <span>{{ opt.label }}</span>
        </label>
      </div>

      <!-- Dropdown -->
      <select
        v-else-if="field.type === 'dropdown'"
        :value="modelValue"
        class="input-field"
        :disabled="disabled"
        @change="$emit('update:modelValue', $event.target.value)"
      >
        <option value="">{{ t('forms.choose') }}</option>
        <option v-for="opt in field.options || []" :key="opt.id" :value="opt.label">{{ opt.label }}</option>
      </select>

      <!-- Linear scale -->
      <div v-else-if="field.type === 'linear_scale'" class="scale-row">
        <span v-if="field.scale_min_label" class="scale-label">{{ field.scale_min_label }}</span>
        <div class="scale-options">
          <label v-for="n in scaleRange" :key="n" class="scale-option">
            <input
              type="radio"
              :name="`scale-${field.id}`"
              :value="n"
              :checked="Number(modelValue) === n"
              :disabled="disabled"
              @change="$emit('update:modelValue', n)"
            />
            <span>{{ n }}</span>
          </label>
        </div>
        <span v-if="field.scale_max_label" class="scale-label">{{ field.scale_max_label }}</span>
      </div>

      <!-- Star rating -->
      <div v-else-if="field.type === 'rating'" class="rating-row" role="radiogroup" :aria-label="field.title">
        <button
          v-for="n in ratingRange"
          :key="n"
          type="button"
          class="rating-star"
          :class="{ active: Number(modelValue) >= n, hoverable: !disabled }"
          :disabled="disabled"
          :aria-label="t('forms.ratingStar', { n })"
          @click="$emit('update:modelValue', n)"
        >
          ★
        </button>
        <span v-if="modelValue" class="rating-value">{{ modelValue }}/{{ field.rating_max || 5 }}</span>
      </div>

      <!-- Grid multiple choice -->
      <div v-else-if="field.type === 'grid_multiple_choice'" class="grid-table-wrap">
        <table class="grid-table">
          <thead>
            <tr>
              <th></th>
              <th v-for="col in field.columns || []" :key="col.id">{{ col.label }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in field.rows || []" :key="row.id">
              <td class="grid-row-label">{{ row.label }}</td>
              <td v-for="col in field.columns || []" :key="col.id">
                <input
                  type="radio"
                  :name="`grid-${field.id}-${row.id}`"
                  :value="col.label"
                  :checked="(modelValue || {})[row.id] === col.label"
                  :disabled="disabled"
                  @change="setGridValue(row.id, col.label)"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Grid checkbox -->
      <div v-else-if="field.type === 'grid_checkbox'" class="grid-table-wrap">
        <table class="grid-table">
          <thead>
            <tr>
              <th></th>
              <th v-for="col in field.columns || []" :key="col.id">{{ col.label }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="row in field.rows || []" :key="row.id">
              <td class="grid-row-label">{{ row.label }}</td>
              <td v-for="col in field.columns || []" :key="col.id">
                <input
                  type="checkbox"
                  :checked="gridCheckboxChecked(row.id, col.label)"
                  :disabled="disabled"
                  @change="toggleGridCheckbox(row.id, col.label)"
                />
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <p v-if="error" class="field-error">{{ error }}</p>
    </template>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

const props = defineProps({
  field: { type: Object, required: true },
  modelValue: { default: '' },
  disabled: { type: Boolean, default: false },
  error: { type: String, default: '' },
  variant: { type: String, default: 'default' },
})

const emit = defineEmits(['update:modelValue'])
const { t } = useI18n()

const variantClass = computed(() => props.variant === 'light' ? 'form-field--light' : '')

const scaleRange = computed(() => {
  const min = props.field.scale_min ?? 1
  const max = props.field.scale_max ?? 5
  const arr = []
  for (let i = min; i <= max; i++) arr.push(i)
  return arr
})

const ratingRange = computed(() => {
  const max = props.field.rating_max ?? 5
  return Array.from({ length: max }, (_, i) => i + 1)
})

function toggleCheckbox(label) {
  const current = [...(props.modelValue || [])]
  const idx = current.indexOf(label)
  if (idx >= 0) current.splice(idx, 1)
  else current.push(label)
  emit('update:modelValue', current)
}

function setGridValue(rowId, colLabel) {
  emit('update:modelValue', { ...(props.modelValue || {}), [rowId]: colLabel })
}

function gridCheckboxChecked(rowId, colLabel) {
  const key = `${rowId}:${colLabel}`
  return (props.modelValue || []).includes(key)
}

function toggleGridCheckbox(rowId, colLabel) {
  const key = `${rowId}:${colLabel}`
  const current = [...(props.modelValue || [])]
  const idx = current.indexOf(key)
  if (idx >= 0) current.splice(idx, 1)
  else current.push(key)
  emit('update:modelValue', current)
}
</script>

<style scoped>
.form-field { margin-bottom: 1.25rem; }
.form-field--error .input-field { border-color: #ef4444; }
.field-label { display: block; font-size: 0.9375rem; font-weight: 600; color: var(--text-primary, #1a1333); margin-bottom: 0.375rem; }
.required-mark { color: #ef4444; margin-inline-start: 0.25rem; }
.field-desc { font-size: 0.8125rem; color: var(--text-secondary, #6b7280); margin-bottom: 0.5rem; }
.field-error { color: #ef4444; font-size: 0.75rem; margin-top: 0.375rem; }
.section-header { padding: 1rem 0 0.5rem; border-bottom: 1px solid var(--border, #e5e7eb); margin-bottom: 0.5rem; }
.section-header__title { font-size: 1.125rem; font-weight: 700; color: var(--text-primary); }
.section-header__desc { font-size: 0.875rem; color: var(--text-secondary); margin-top: 0.25rem; }
.description-block { padding: 0.75rem; background: var(--bg-subtle, #f9fafb); border-radius: 0.5rem; }
.description-block__title { font-weight: 600; margin-bottom: 0.25rem; }
.description-block__text { font-size: 0.875rem; color: var(--text-secondary); }
.choice-list { display: flex; flex-direction: column; gap: 0.5rem; }
.choice-item { display: flex; align-items: center; gap: 0.5rem; font-size: 0.875rem; cursor: pointer; }
.scale-row { display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap; }
.scale-options { display: flex; gap: 0.5rem; }
.scale-option { display: flex; flex-direction: column; align-items: center; gap: 0.25rem; font-size: 0.75rem; cursor: pointer; }
.scale-label { font-size: 0.75rem; color: var(--text-secondary); max-width: 6rem; }
.rating-row { display: flex; align-items: center; gap: 0.25rem; flex-wrap: wrap; }
.rating-star {
  border: none; background: none; font-size: 1.75rem; line-height: 1;
  color: #d1d5db; cursor: pointer; padding: 0.125rem; transition: color 0.15s, transform 0.15s;
}
.rating-star.active { color: #f59e0b; }
.rating-star.hoverable:hover { color: #fbbf24; transform: scale(1.08); }
.rating-star:disabled { cursor: not-allowed; opacity: 0.7; }
.rating-value { font-size: 0.8125rem; color: var(--text-secondary); margin-inline-start: 0.5rem; }
.form-field--light .rating-star { color: #d1d5db; }
.form-field--light .rating-star.active { color: #f59e0b; }
.grid-table-wrap { overflow-x: auto; }
.grid-table { width: 100%; border-collapse: collapse; font-size: 0.8125rem; }
.grid-table th, .grid-table td { padding: 0.5rem; text-align: center; border: 1px solid var(--border, #e5e7eb); }
.grid-row-label { text-align: start !important; font-weight: 500; }
.form-field--light .field-label,
.form-field--light .section-header__title,
.form-field--light .description-block__title { color: #1a1333; }
.form-field--light .field-desc,
.form-field--light .section-header__desc,
.form-field--light .description-block__text,
.form-field--light .choice-item,
.form-field--light .scale-label,
.form-field--light .scale-option,
.form-field--light .grid-row-label,
.form-field--light .grid-table th { color: #374151; }
.form-field--light .description-block { background: #f9fafb; }
.form-field--light .grid-table th,
.form-field--light .grid-table td { border-color: #e5e7eb; }
.form-field--light .input-field {
  background: #ffffff;
  color: #1a1333;
  border-color: #e5e7eb;
}
.form-field--light .input-field::placeholder { color: #9ca3af; }
</style>
