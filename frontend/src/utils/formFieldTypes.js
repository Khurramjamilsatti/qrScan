export const FIELD_TYPES = [
  { id: 'short_text', icon: 'Aa', labelKey: 'forms.fieldTypes.shortText', group: 'text' },
  { id: 'paragraph', icon: '¶', labelKey: 'forms.fieldTypes.paragraph', group: 'text' },
  { id: 'multiple_choice', icon: '◉', labelKey: 'forms.fieldTypes.multipleChoice', group: 'choice' },
  { id: 'checkboxes', icon: '☑', labelKey: 'forms.fieldTypes.checkboxes', group: 'choice' },
  { id: 'dropdown', icon: '▾', labelKey: 'forms.fieldTypes.dropdown', group: 'choice' },
  { id: 'linear_scale', icon: '—', labelKey: 'forms.fieldTypes.linearScale', group: 'scale' },
  { id: 'rating', icon: '★', labelKey: 'forms.fieldTypes.rating', group: 'scale' },
  { id: 'grid_multiple_choice', icon: '▦', labelKey: 'forms.fieldTypes.gridMultipleChoice', group: 'grid' },
  { id: 'grid_checkbox', icon: '▣', labelKey: 'forms.fieldTypes.gridCheckbox', group: 'grid' },
  { id: 'date', icon: '📅', labelKey: 'forms.fieldTypes.date', group: 'date' },
  { id: 'time', icon: '🕐', labelKey: 'forms.fieldTypes.time', group: 'date' },
  { id: 'email', icon: '✉', labelKey: 'forms.fieldTypes.email', group: 'text' },
  { id: 'number', icon: '#', labelKey: 'forms.fieldTypes.number', group: 'text' },
  { id: 'url', icon: '🔗', labelKey: 'forms.fieldTypes.url', group: 'text' },
  { id: 'section_header', icon: '§', labelKey: 'forms.fieldTypes.sectionHeader', group: 'layout' },
  { id: 'description_text', icon: 'ℹ', labelKey: 'forms.fieldTypes.descriptionText', group: 'layout' },
]

export function generateFieldId() {
  return 'f_' + Math.random().toString(36).slice(2, 10)
}

export function defaultOption(label = '') {
  return { id: generateFieldId(), label }
}

export function createField(type) {
  const base = {
    id: generateFieldId(),
    type,
    title: '',
    description: '',
    required: false,
  }

  switch (type) {
    case 'multiple_choice':
    case 'checkboxes':
    case 'dropdown':
      return { ...base, options: [defaultOption('Option 1'), defaultOption('Option 2')] }
    case 'linear_scale':
      return { ...base, scale_min: 1, scale_max: 5, scale_min_label: '', scale_max_label: '' }
    case 'rating':
      return { ...base, rating_max: 5 }
    case 'grid_multiple_choice':
    case 'grid_checkbox':
      return {
        ...base,
        rows: [defaultOption('Row 1'), defaultOption('Row 2')],
        columns: [defaultOption('Col 1'), defaultOption('Col 2'), defaultOption('Col 3')],
      }
    case 'section_header':
    case 'description_text':
      return { ...base, required: false }
    default:
      return base
  }
}

export function defaultFormSettings() {
  return {
    collect_email: false,
    confirmation_message: '',
    redirect_url: '/app/forms',
    show_progress_bar: true,
    show_submit_another: true,
    shuffle_questions: false,
  }
}

export function defaultForm() {
  return {
    slug: '',
    title: 'Untitled Form',
    description: '',
    fields: [
      createField('short_text'),
    ],
    settings: defaultFormSettings(),
    theme_color: '#673ab7',
    background_color: '#f3f0ff',
    header_image_path: '',
    logo_path: '',
    background_image_path: '',
    qr_shape: 'square',
    dot_style: 'square',
    corner_style: 'sharp',
    frame_style: 'none',
    custom_domain_id: null,
    closes_at: '',
    max_submissions: 0,
    max_submissions_per_respondent: 1,
  }
}

export function isInputField(type) {
  return !['section_header', 'description_text'].includes(type)
}

export function emptyResponses(fields) {
  const responses = {}
  for (const field of fields || []) {
    if (!isInputField(field.type)) continue
    if (field.type === 'checkboxes' || field.type === 'grid_checkbox') {
      responses[field.id] = []
    } else if (field.type === 'grid_multiple_choice') {
      responses[field.id] = {}
    } else {
      responses[field.id] = ''
    }
  }
  return responses
}
