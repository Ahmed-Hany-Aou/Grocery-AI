import React from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Camera, PackagePlus, ShoppingBag, Settings, LogOut, History, Check, ShieldAlert } from 'lucide-react';
import CameraOverlay from '../components/CameraOverlay';
import { visionService, apiService } from '../services/api';
import { QRCodeSVG } from 'qrcode.react';

const Dashboard = ({ onLogout }) => {
  const [showCamera, setShowCamera] = useState(false);
  const [activeMode, setActiveMode] = useState(null); // 'checkout' or 'stock'
  const [aiResult, setAiResult] = useState(null);
  const [showInvoice, setShowInvoice] = useState(false);
  
  const isArabic = true;

  const cards = [
    {
      id: 'checkout',
      title: isArabic ? "عملية بيع" : "Checkout",
      subtitle: isArabic ? "تصوير المشتريات والبيع" : "Scan items & sell",
      icon: <ShoppingBag size={48} />,
      color: "bg-brand-primary",
      textColor: "text-white",
      primary: true
    },
    {
      id: 'stock',
      title: isArabic ? "إضافة مخزون" : "Add Stock",
      subtitle: isArabic ? "تسجيل بضاعة جديدة" : "Record new products",
      icon: <PackagePlus size={48} />,
      color: "bg-brand-dark",
      textColor: "text-white",
      primary: true
    }
  ];

  return (
    <div className="min-h-screen p-6 font-arabic bg-slate-50 flex flex-col" dir="rtl">
      {/* Top Header */}
      <div className="flex justify-between items-center mb-10 mt-4">
        <div>
          <h2 className="text-2xl font-bold text-gray-800">أهلاً يا محمد 👋</h2>
          <p className="text-gray-500">متجر البقالة الذكي</p>
        </div>
        <button 
          onClick={onLogout}
          className="w-12 h-12 rounded-2xl bg-white border border-gray-100 flex items-center justify-center text-red-500 shadow-sm active:scale-95 transition-all"
        >
          <LogOut size={24} />
        </button>
      </div>

      {/* Main Actions */}
      <div className="flex flex-col gap-6 flex-1">
        {cards.map((card, index) => (
          <motion.button
            key={card.id}
            initial={{ opacity: 0, x: -20 }}
            animate={{ opacity: 1, x: 0 }}
            transition={{ delay: index * 0.1 }}
            onClick={() => {
              setActiveMode(card.id);
              setShowCamera(true);
            }}
            className={`${card.color} ${card.textColor} p-8 rounded-[40px] flex flex-col justify-between items-start h-64 shadow-xl active:scale-[0.98] transition-all text-right relative overflow-hidden`}
          >
            {/* Background Decoration */}
            <div className="absolute -right-10 -bottom-10 opacity-20 transform scale-150">
              {card.icon}
            </div>

            <div className="bg-white bg-opacity-20 p-4 rounded-3xl">
              {card.icon}
            </div>
            
            <div className="z-10 mt-4">
              <h3 className="text-4xl font-black mb-2">{card.title}</h3>
              <p className="text-lg opacity-80">{card.subtitle}</p>
            </div>
          </motion.button>
        ))}
      </div>

      {/* Bottom Quick Links */}
      <div className="grid grid-cols-2 gap-4 mt-8 pb-4">
        <button className="bg-white p-6 rounded-3xl flex flex-col items-center gap-2 border border-gray-100 shadow-sm active:scale-95 transition-all">
          <History className="text-gray-400" />
          <span className="font-bold text-gray-600">الفواتير</span>
        </button>
        <button className="bg-white p-6 rounded-3xl flex flex-col items-center gap-2 border border-gray-100 shadow-sm active:scale-95 transition-all">
          <Settings className="text-gray-400" />
          <span className="font-bold text-gray-600">الإعدادات</span>
        </button>
      </div>

      {/* Overlays */}
      <AnimatePresence>
        {showCamera && (
          <CameraOverlay 
            onClose={() => setShowCamera(false)}
            onCapture={async (blob) => {
              try {
                const command = activeMode === 'stock' ? 'إضافة للمخزن' : 'عملية بيع';
                const result = await visionService.extractProducts(blob, command);
                setAiResult(result);
                setShowCamera(false);
              } catch (err) {
                alert("خطأ في الاتصال بالذكاء الاصطناعي");
                setShowCamera(false);
              }
            }} 
          />
        )}

        {aiResult && (
          <ReviewScreen 
            data={aiResult} 
            mode={activeMode}
            onCancel={() => setAiResult(null)}
            onConfirm={async (finalData) => {
              if (activeMode === 'stock') {
                await apiService.bulkUpdateStock(finalData.products.map(p => ({
                  id: p.id,
                  quantity: p.ai_quantity,
                  mode: 'add'
                })));
                setAiResult(null);
                alert("تم تحديث المخزن بنجاح");
              } else {
                setShowInvoice(true);
              }
            }}
          />
        )}
        
        {showInvoice && (
          <InvoiceOverlay 
            data={aiResult} 
            onClose={() => {
              setShowInvoice(false);
              setAiResult(null);
            }} 
          />
        )}
      </AnimatePresence>
    </div>
  );
};

/* --- Sub-Components --- */

const ReviewScreen = ({ data, mode, onCancel, onConfirm }) => {
  return (
    <motion.div 
      initial={{ y: '100%' }}
      animate={{ y: 0 }}
      exit={{ y: '100%' }}
      className="fixed inset-0 z-40 bg-brand-light flex flex-col font-arabic"
      dir="rtl"
    >
      <div className="p-6 border-b bg-white flex justify-between items-center">
        <h2 className="text-2xl font-bold">مراجعة البيانات</h2>
        <button onClick={onCancel} className="text-gray-400">إلغاء</button>
      </div>

      <div className="p-6 flex-1 overflow-y-auto">
        <div className="flex items-center gap-3 p-4 bg-brand-primary/10 rounded-2xl mb-6">
          <Check className="text-brand-primary" />
          <p className="font-medium text-lg">تحليلك جاهز يا محمد. هل البيانات صحيحة؟</p>
        </div>

        <div className="space-y-4">
          {data.products.map((item, i) => (
            <div key={i} className="bg-white p-5 rounded-3xl shadow-sm border border-gray-100 flex justify-between items-center">
              <div>
                <h4 className="text-xl font-bold">{item.name_ar || item.name}</h4>
                <p className="text-gray-500">سعر الوحدة: {item.ai_unit_price} ج.م</p>
              </div>
              <div className="flex items-center gap-4">
                <div className="bg-gray-100 px-4 py-2 rounded-xl text-xl font-bold">
                   × {item.ai_quantity}
                </div>
              </div>
            </div>
          ))}
        </div>
      </div>

      <div className="p-6 space-y-4 bg-white shadow-2xl rounded-t-[40px]">
        <div className="flex justify-between items-center px-4 mb-2">
           <span className="text-xl text-gray-500">الإجمالي:</span>
           <span className="text-3xl font-black text-brand-dark">
             {data.products.reduce((acc, p) => acc + (p.ai_quantity * p.ai_unit_price), 0)} ج.م
           </span>
        </div>
        <button 
          onClick={() => onConfirm(data)}
          className="btn-primary w-full py-6 text-2xl"
        >
          {mode === 'stock' ? 'تأكيد إضافة المخزن' : 'إتمام عملية البيع'}
        </button>
      </div>
    </motion.div>
  );
};

const InvoiceOverlay = ({ data, onClose }) => {
  const invoiceUrl = `https://wa.me/?text=فاتورة بقالة:%0A${data.products.map(p => `${p.name_ar} x ${p.ai_quantity}`).join('%0A')}`;
  
  return (
    <motion.div 
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
      className="fixed inset-0 z-50 bg-brand-dark flex items-center justify-center p-6"
    >
      <div className="bg-white w-full max-w-sm rounded-[40px] p-8 text-center relative">
        <button onClick={onClose} className="absolute top-6 left-6 text-gray-300"><X /></button>
        
        <div className="bg-brand-primary/20 w-20 h-20 rounded-full flex items-center justify-center mx-auto mb-6">
          <Check className="text-brand-primary" size={40} />
        </div>
        
        <h2 className="text-3xl font-black mb-4">تم البيع بنجاح!</h2>
        <p className="text-gray-500 mb-8">اجعل الزبون يمسح الكود للحصول على الفاتورة عبر واتساب</p>
        
        <div className="bg-slate-50 p-6 rounded-[32px] mb-8 inline-block border-2 border-brand-primary/10">
          <QRCodeSVG value={invoiceUrl} size={200} />
        </div>
        
        <button onClick={onClose} className="btn-secondary w-full">إغلاق القائمة</button>
      </div>
    </motion.div>
  );
};

export default Dashboard;
