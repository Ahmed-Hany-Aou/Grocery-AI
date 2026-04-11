import React, { createContext, useState, useContext, useEffect } from 'react';

const LanguageContext = createContext();

const translations = {
  ar: {
    welcome: "أهلاً بك يا محمد",
    instruction: "أدخل رقمك السري للمتابعة",
    error_pin: "رقم سري غير صحيح",
    sale_mode: "عملية بيع",
    sale_desc: "تصوير المشتريات والبيع",
    stock_mode: "إضافة مخزون",
    stock_desc: "تسجيل بضاعة جديدة",
    invoices: "الفواتير",
    settings: "الإعدادات",
    greeting: "أهلاً يا محمد 👋",
    app_subtitle: "متجر البقالة الذكي",
    review_title: "مراجعة البيانات",
    cancel: "إلغاء",
    ai_status: "تحليلك جاهز يا محمد. هل البيانات صحيحة؟",
    unit_price: "سعر الوحدة",
    total: "الإجمالي",
    confirm_stock: "تأكيد إضافة المخزن",
    confirm_sale: "إتمام عملية البيع",
    success_title: "تم البيع بنجاح!",
    success_desc: "اجعل الزبون يمسح الكود للحصول على الفاتورة عبر واتساب",
    close_menu: "إغلاق القائمة",
    camera_status: "محمد يراقب البضاعة...",
    ai_analyzing: "جاري التحليل بالذكاء الاصطناعي...",
    allow_camera: "يرجى السماح بالوصول للكاميرا",
    ai_error: "خطأ في الاتصال بالذكاء الاصطناعي",
    stock_success: "تم تحديث المخزن بنجاح",
    lang_toggle: "English"
  },
  en: {
    welcome: "Welcome, Mohammed",
    instruction: "Enter your PIN to continue",
    error_pin: "Incorrect PIN",
    sale_mode: "Checkout",
    sale_desc: "Scan items & sell",
    stock_mode: "Add Stock",
    stock_desc: "Record new products",
    invoices: "Invoices",
    settings: "Settings",
    greeting: "Hello, Mohammed 👋",
    app_subtitle: "Smart Grocery Store",
    review_title: "Review Data",
    cancel: "Cancel",
    ai_status: "Your analysis is ready, Mohammed. Is the data correct?",
    unit_price: "Unit Price",
    total: "Total",
    confirm_stock: "Confirm Stock Addition",
    confirm_sale: "Complete Transaction",
    success_title: "Sale Successful!",
    success_desc: "Ask the customer to scan the code for the WhatsApp invoice",
    close_menu: "Close Menu",
    camera_status: "Mohammed is watching items...",
    ai_analyzing: "Analyzing with AI...",
    allow_camera: "Please allow camera access",
    ai_error: "AI Connection Error",
    stock_success: "Stock updated successfully",
    lang_toggle: "عربي"
  }
};

export const LanguageProvider = ({ children }) => {
  const [isArabic, setIsArabic] = useState(true);

  const t = (key) => {
    return translations[isArabic ? 'ar' : 'en'][key] || key;
  };

  const formatPrice = (price) => {
    if (isArabic) return `${price} ج.م`;
    return `EGP ${price}`;
  };

  const dir = isArabic ? 'rtl' : 'ltr';
  const fontFamily = isArabic ? "'IBM Plex Sans Arabic', sans-serif" : "'Inter', sans-serif";

  // Apply global direction and font
  useEffect(() => {
    document.documentElement.dir = dir;
    document.documentElement.lang = isArabic ? 'ar' : 'en';
    document.body.style.fontFamily = fontFamily;
  }, [isArabic, dir, fontFamily]);

  return (
    <LanguageContext.Provider value={{ isArabic, setIsArabic, t, formatPrice, dir }}>
      {children}
    </LanguageContext.Provider>
  );
};

export const useLanguage = () => useContext(LanguageContext);
