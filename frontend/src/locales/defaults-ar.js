/** Arabic default content for new digital pages, menus, badges, and campaigns */
export const AR_PAGE_DEFAULTS = {
  landing: {
    headline: 'نمِّ عملك عبر الإنترنت',
    subheadline: 'كل ما تحتاجه للإطلاق والتسويق والنمو.',
    cta_label: 'ابدأ الآن',
    cta_url: 'https://',
    features: [
      { title: 'إعداد سريع', description: 'أطلق في دقائق، لا أسابيع.' },
      { title: 'تتبع النتائج', description: 'اعرف من يزور ويتحول.' },
      { title: 'شارك في كل مكان', description: 'رابط واحد لكل قناة.' },
    ],
  },
  portfolio: {
    headline: 'محفظة إبداعية',
    about: 'مصمم ومطور يصنع تجارب رقمية.',
    projects: [
      { title: 'إعادة هوية العلامة', description: 'هوية بصرية لشركة تقنية ناشئة.', image_path: '' },
      { title: 'تطبيق جوال', description: 'واجهة وتجربة مستخدم لمنصة لياقة.', image_path: '' },
    ],
  },
  event: {
    event_name: 'المؤتمر السنوي 2026',
    date: '15 يونيو 2026 · 9:00 ص',
    location: 'سان فرancisco، CA',
    description: 'انضم إلى قادة الصناعة ليوم من المحاضرات وورش العمل والتواصل.',
    cta_label: 'سجّل الآن',
    cta_url: 'https://',
  },
  simple: {
    headline: 'مرحباً',
    body: 'شارك التحديثات والإعلانات أو أي رسالة في صفحة نظيفة ومركزة.',
    cta_label: '',
    cta_url: '',
  },
}

export const AR_MENU_SECTIONS = [
  {
    name: 'المقبلات',
    items: [
      { name: 'حساء اليوم', description: 'اختيار الشيف الموسمي', price: '8.00', image_path: '', tags: ['vegetarian'] },
      { name: 'بروشيتا', description: 'طماطم وريحان وزيت زيتون على خبز محمص', price: '9.50', image_path: '', tags: ['vegetarian'] },
    ],
  },
  {
    name: 'الأطباق الرئيسية',
    items: [
      { name: 'سلمون مشوي', description: 'مع زبدة الليمون وخضروات موسمية', price: '22.00', image_path: '', tags: ['gluten-free'] },
      { name: 'معكرونة بالخضار', description: 'خضروات طازجة بزيت زيتون وثوم', price: '16.00', image_path: '', tags: ['vegetarian', 'vegan'] },
    ],
  },
]

export const AR_BADGE_DEFAULTS = {
  title: 'شارة إنجاز',
}

export const AR_SCAN_TO_WIN_DEFAULTS = {
  win_message: 'تهانينا! لقد فزت!',
  lose_message: 'حظاً أوفر في المرة القادمة!',
}
