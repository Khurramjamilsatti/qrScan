import { useDialogStore } from '../stores/dialog'

export function useDialog() {
  const store = useDialogStore()
  return {
    alert: store.alert,
    confirm: store.confirm,
  }
}
