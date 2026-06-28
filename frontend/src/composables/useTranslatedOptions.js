import { computed } from 'vue'
import { useI18n } from 'vue-i18n'

/** Map option arrays with labelKey to translated { value, label } pairs */
export function useTranslatedOptions(options, keyField = 'labelKey', valueField = 'value') {
  const { t } = useI18n()
  return computed(() =>
    options.map((opt) => ({
      ...opt,
      label: t(opt[keyField] || opt.label),
      ...(opt[valueField] !== undefined ? { [valueField]: opt[valueField] } : {}),
    }))
  )
}

/** Translate a list of items that have labelKey */
export function translateList(items, t, keyField = 'labelKey') {
  return items.map((item) => ({
    ...item,
    label: t(item[keyField] || item.label),
    ...(item.descriptionKey ? { description: t(item.descriptionKey) } : {}),
  }))
}
