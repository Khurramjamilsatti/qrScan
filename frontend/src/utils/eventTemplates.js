export const EVENT_TEMPLATE_CATEGORIES = [
  { id: 'all', labelKey: 'templates.categories.all' },
  { id: 'wedding', labelKey: 'templates.eventCategories.wedding' },
  { id: 'birthday', labelKey: 'templates.eventCategories.birthday' },
  { id: 'celebration', labelKey: 'templates.eventCategories.celebration' },
  { id: 'corporate', labelKey: 'templates.eventCategories.corporate' },
  { id: 'holiday', labelKey: 'templates.eventCategories.holiday' },
  { id: 'gift', labelKey: 'templates.eventCategories.gift' },
  { id: 'memorial', labelKey: 'templates.eventCategories.memorial' },
]

export const EVENT_TEMPLATES = [
  { id: 'simple-invite', category: 'celebration', layout: 'simple', labelKey: 'templates.event.simpleInvite.label', descriptionKey: 'templates.event.simpleInvite.description', icon: '✉️', premium: false, popular: true },
  { id: 'birthday-bash', category: 'birthday', layout: 'birthday', labelKey: 'templates.event.birthdayBash.label', descriptionKey: 'templates.event.birthdayBash.description', icon: '🎂', premium: false, popular: true },
  { id: 'classic-wedding', category: 'wedding', layout: 'wedding', labelKey: 'templates.event.classicWedding.label', descriptionKey: 'templates.event.classicWedding.description', icon: '💍', premium: false, popular: true },
  { id: 'wedding-elegant', category: 'wedding', layout: 'wedding', labelKey: 'templates.event.weddingElegant.label', descriptionKey: 'templates.event.weddingElegant.description', icon: '✨', premium: true, thumbGradient: 'linear-gradient(145deg, #f5f0e8 0%, #c9a227 100%)' },
  { id: 'wedding-modern', category: 'wedding', layout: 'wedding', labelKey: 'templates.event.weddingModern.label', descriptionKey: 'templates.event.weddingModern.description', icon: '💫', premium: true, thumbGradient: 'linear-gradient(145deg, #1a1333 0%, #6b4fa0 100%)' },
  { id: 'wedding-romantic', category: 'wedding', layout: 'wedding', labelKey: 'templates.event.weddingRomantic.label', descriptionKey: 'templates.event.weddingRomantic.description', icon: '🌹', premium: true, thumbGradient: 'linear-gradient(145deg, #be185d 0%, #fda4af 100%)' },
  { id: 'wedding-minimal', category: 'wedding', layout: 'wedding', labelKey: 'templates.event.weddingMinimal.label', descriptionKey: 'templates.event.weddingMinimal.description', icon: '◇', premium: true, thumbGradient: 'linear-gradient(145deg, #faf8fd 0%, #e8e4f0 100%)' },
  { id: 'desi-wedding', category: 'wedding', layout: 'wedding', labelKey: 'templates.event.desiWedding.label', descriptionKey: 'templates.event.desiWedding.description', icon: '🪔', premium: true, thumbGradient: 'linear-gradient(145deg, #7c2d12 0%, #fbbf24 100%)' },
  { id: 'birthday-kids', category: 'birthday', layout: 'birthday', labelKey: 'templates.event.birthdayKids.label', descriptionKey: 'templates.event.birthdayKids.description', icon: '🎈', premium: true, thumbGradient: 'linear-gradient(145deg, #2563eb 0%, #f472b6 100%)' },
  { id: 'birthday-elegant', category: 'birthday', layout: 'birthday', labelKey: 'templates.event.birthdayElegant.label', descriptionKey: 'templates.event.birthdayElegant.description', icon: '🥂', premium: true, thumbGradient: 'linear-gradient(145deg, #1e3a5f 0%, #c9a227 100%)' },
  { id: 'surprise-party', category: 'birthday', layout: 'birthday', labelKey: 'templates.event.surpriseParty.label', descriptionKey: 'templates.event.surpriseParty.description', icon: '🎉', premium: true, thumbGradient: 'linear-gradient(145deg, #7c3aed 0%, #f97316 100%)' },
  { id: 'baby-shower', category: 'celebration', layout: 'celebration', labelKey: 'templates.event.babyShower.label', descriptionKey: 'templates.event.babyShower.description', icon: '👶', premium: true, thumbGradient: 'linear-gradient(145deg, #fce7f3 0%, #a5b4fc 100%)' },
  { id: 'gender-reveal', category: 'celebration', layout: 'celebration', labelKey: 'templates.event.genderReveal.label', descriptionKey: 'templates.event.genderReveal.description', icon: '🎀', premium: true, thumbGradient: 'linear-gradient(145deg, #60a5fa 0%, #f9a8d4 100%)' },
  { id: 'graduation', category: 'celebration', layout: 'celebration', labelKey: 'templates.event.graduation.label', descriptionKey: 'templates.event.graduation.description', icon: '🎓', premium: true, thumbGradient: 'linear-gradient(145deg, #1e3a5f 0%, #64748b 100%)' },
  { id: 'farewell', category: 'celebration', layout: 'celebration', labelKey: 'templates.event.farewell.label', descriptionKey: 'templates.event.farewell.description', icon: '👋', premium: true, thumbGradient: 'linear-gradient(145deg, #0f766e 0%, #5eead4 100%)' },
  { id: 'corporate-event', category: 'corporate', layout: 'corporate', labelKey: 'templates.event.corporateEvent.label', descriptionKey: 'templates.event.corporateEvent.description', icon: '🏢', premium: true, thumbGradient: 'linear-gradient(145deg, #0f172a 0%, #334155 100%)' },
  { id: 'retirement', category: 'corporate', layout: 'corporate', labelKey: 'templates.event.retirement.label', descriptionKey: 'templates.event.retirement.description', icon: '🌅', premium: true, thumbGradient: 'linear-gradient(145deg, #78350f 0%, #fbbf24 100%)' },
  { id: 'eid-greeting', category: 'holiday', layout: 'holiday', labelKey: 'templates.event.eidGreeting.label', descriptionKey: 'templates.event.eidGreeting.description', icon: '🌙', premium: true, thumbGradient: 'linear-gradient(145deg, #14532d 0%, #fbbf24 100%)' },
  { id: 'christmas-card', category: 'holiday', layout: 'holiday', labelKey: 'templates.event.christmasCard.label', descriptionKey: 'templates.event.christmasCard.description', icon: '🎄', premium: true, thumbGradient: 'linear-gradient(145deg, #14532d 0%, #dc2626 100%)' },
  { id: 'new-year', category: 'holiday', layout: 'holiday', labelKey: 'templates.event.newYear.label', descriptionKey: 'templates.event.newYear.description', icon: '🎆', premium: true, thumbGradient: 'linear-gradient(145deg, #1e1b4b 0%, #6366f1 100%)' },
  { id: 'memorial', category: 'memorial', layout: 'memorial', labelKey: 'templates.event.memorial.label', descriptionKey: 'templates.event.memorial.description', icon: '🕊', premium: true, thumbGradient: 'linear-gradient(145deg, #374151 0%, #9ca3af 100%)' },
  { id: 'digital-gift-card', category: 'gift', layout: 'gift', labelKey: 'templates.event.digitalGiftCard.label', descriptionKey: 'templates.event.digitalGiftCard.description', icon: '🎁', premium: true, thumbGradient: 'linear-gradient(145deg, #be185d 0%, #f472b6 100%)' },
]

export const FREE_EVENT_TEMPLATES = EVENT_TEMPLATES.filter((t) => !t.premium).map((t) => t.id)

export function getEventTemplateLayout(templateId) {
  const tpl = EVENT_TEMPLATES.find((t) => t.id === templateId)
  return tpl?.layout || 'simple'
}

export function isPremiumEventTemplate(templateId) {
  const tpl = EVENT_TEMPLATES.find((t) => t.id === templateId)
  return !!tpl?.premium
}

export function defaultEventTypeForTemplate(templateId) {
  const map = {
    'simple-invite': 'general',
    'classic-wedding': 'wedding',
    'wedding-elegant': 'wedding',
    'wedding-modern': 'wedding',
    'wedding-romantic': 'wedding',
    'wedding-minimal': 'wedding',
    'desi-wedding': 'wedding',
    'birthday-bash': 'birthday',
    'birthday-kids': 'birthday',
    'birthday-elegant': 'birthday',
    'surprise-party': 'birthday',
    'baby-shower': 'baby-shower',
    'gender-reveal': 'baby-shower',
    'graduation': 'graduation',
    'farewell': 'farewell',
    'corporate-event': 'corporate',
    'retirement': 'corporate',
    'eid-greeting': 'holiday',
    'christmas-card': 'holiday',
    'new-year': 'holiday',
    'memorial': 'memorial',
    'digital-gift-card': 'gift',
  }
  return map[templateId] || 'general'
}
