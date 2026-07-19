export const MENU_TEMPLATE_CATEGORIES = [
  { id: 'all', labelKey: 'templates.categories.all' },
  { id: 'restaurant', labelKey: 'templates.categories.restaurant' },
  { id: 'cafe', labelKey: 'templates.categories.cafe' },
  { id: 'bar', labelKey: 'templates.categories.bar' },
]

export const MENU_TEMPLATES = [
  { id: 'classic', category: 'restaurant', layout: 'classic', labelKey: 'templates.menu.classic.label', descriptionKey: 'templates.menu.classic.description', icon: '🍽', thumbnail: 'https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=480&h=300&fit=crop', popular: true },
  { id: 'restaurant', category: 'restaurant', layout: 'classic', labelKey: 'templates.menu.restaurant.label', descriptionKey: 'templates.menu.restaurant.description', icon: '🥘', thumbnail: 'https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=480&h=300&fit=crop', popular: true },
  { id: 'bistro', category: 'restaurant', layout: 'bistro', labelKey: 'templates.menu.bistro.label', descriptionKey: 'templates.menu.bistro.description', icon: '🍷', thumbnail: 'https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=480&h=300&fit=crop' },
  { id: 'elegant', category: 'restaurant', layout: 'bistro', labelKey: 'templates.menu.elegant.label', descriptionKey: 'templates.menu.elegant.description', icon: '✨', thumbnail: 'https://images.unsplash.com/photo-1559339352-11d035aa65de?w=480&h=300&fit=crop' },
  { id: 'modern', category: 'cafe', layout: 'modern', labelKey: 'templates.menu.modern.label', descriptionKey: 'templates.menu.modern.description', icon: '☕', thumbnail: 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?w=480&h=300&fit=crop', popular: true },
  { id: 'cafe', category: 'cafe', layout: 'modern', labelKey: 'templates.menu.cafe.label', descriptionKey: 'templates.menu.cafe.description', icon: '🥐', thumbnail: 'https://images.unsplash.com/photo-1501339847302-ac426a4a7cbb?w=480&h=300&fit=crop' },
  { id: 'brunch', category: 'cafe', layout: 'modern', labelKey: 'templates.menu.brunch.label', descriptionKey: 'templates.menu.brunch.description', icon: '🥞', thumbnail: 'https://images.unsplash.com/photo-1525351484163-7529414344d8?w=480&h=300&fit=crop' },
  { id: 'minimal', category: 'bar', layout: 'minimal', labelKey: 'templates.menu.minimal.label', descriptionKey: 'templates.menu.minimal.description', icon: '◻️', thumbnail: 'https://images.unsplash.com/photo-1572116469696-31de0f17cc34?w=480&h=300&fit=crop', popular: true },
  { id: 'compact', category: 'bar', layout: 'minimal', labelKey: 'templates.menu.compact.label', descriptionKey: 'templates.menu.compact.description', icon: '📋', thumbnail: 'https://images.unsplash.com/photo-1514933651103-005eec06c04b?w=480&h=300&fit=crop' },
  { id: 'grid', category: 'bar', layout: 'grid', labelKey: 'templates.menu.grid.label', descriptionKey: 'templates.menu.grid.description', icon: '▦', thumbnail: 'https://images.unsplash.com/photo-1551218808-94e220e084d2?w=480&h=300&fit=crop' },
]

export function getMenuTemplateLayout(templateId) {
  const tpl = MENU_TEMPLATES.find((t) => t.id === templateId)
  return tpl?.layout || templateId || 'classic'
}
