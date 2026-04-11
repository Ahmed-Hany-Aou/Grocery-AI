import React, { useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { Fingerprint, Delete, Languages, CheckCircle2 } from 'lucide-react';

const LoginPage = ({ onLogin }) => {
  const [pin, setPin] = useState('');
  const [isArabic, setIsArabic] = useState(true);
  const [error, setError] = useState(false);

  const handleNumberClick = (num) => {
    if (pin.length < 4) {
      setPin(prev => prev + num);
      setError(false);
    }
  };

  const handleDelete = () => {
    setPin(prev => prev.slice(0, -1));
  };

  useEffect(() => {
    if (pin.length === 4) {
      // Simulate validation
      if (pin === '1234') {
        onLogin();
      } else {
        setError(true);
        setTimeout(() => setPin(''), 500);
      }
    }
  }, [pin, onLogin]);

  const t = {
    ar: {
      welcome: "أهلاً بك يا محمد",
      instruction: "أدخل رقمك السري للمتابعة",
      error: "رقم سري غير صحيح",
      lang: "English"
    },
    en: {
      welcome: "Welcome, Mohammed",
      instruction: "Enter your PIN to continue",
      error: "Incorrect PIN",
      lang: "عربي"
    }
  };

  const currentT = isArabic ? t.ar : t.en;

  return (
    <div className={`min-h-screen flex flex-col items-center justify-between p-8 bg-brand-light pb-12 ${isArabic ? 'font-arabic' : 'font-sans'}`} dir={isArabic ? 'rtl' : 'ltr'}>
      {/* Header */}
      <div className="text-center mt-12">
        <motion.h1 
          initial={{ opacity: 0, y: -20 }}
          animate={{ opacity: 1, y: 0 }}
          className="text-4xl font-bold text-brand-dark mb-2"
        >
          {currentT.welcome}
        </motion.h1>
        <p className="text-gray-500 text-lg">{currentT.instruction}</p>
      </div>

      {/* PIN Indicators */}
      <div className="flex gap-6 my-8">
        {[1, 2, 3, 4].map((i) => (
          <motion.div
            key={i}
            animate={pin.length >= i ? { scale: 1.2, backgroundColor: "#84CC16" } : { scale: 1, backgroundColor: "#E2E8F0" }}
            className={`w-5 h-5 rounded-full transition-colors duration-200 ${error ? 'animate-shake bg-red-500' : ''}`}
          />
        ))}
      </div>

      {/* Number Pad */}
      <div className="grid grid-cols-3 gap-6 w-full max-w-sm">
        {[1, 2, 3, 4, 5, 6, 7, 8, 9].map((num) => (
          <button
            key={num}
            onClick={() => handleNumberClick(num.toString())}
            className="w-full aspect-square text-3xl font-bold bg-white rounded-3xl shadow-sm hover:bg-gray-50 active:scale-90 transition-all border border-gray-100 flex items-center justify-center"
          >
            {num}
          </button>
        ))}
        <button 
          className="w-full aspect-square text-brand-primary flex items-center justify-center active:scale-90 transition-all rounded-3xl"
          onClick={() => alert("Biometrics not available in this browser")}
        >
          <Fingerprint size={48} />
        </button>
        <button
          onClick={() => handleNumberClick('0')}
          className="w-full aspect-square text-3xl font-bold bg-white rounded-3xl shadow-sm hover:bg-gray-50 active:scale-90 transition-all border border-gray-100 flex items-center justify-center"
        >
          0
        </button>
        <button
          onClick={handleDelete}
          className="w-full aspect-square text-gray-400 flex items-center justify-center active:scale-90 transition-all rounded-3xl"
        >
          <Delete size={36} />
        </button>
      </div>

      {/* Language Toggle */}
      <button 
        onClick={() => setIsArabic(!isArabic)}
        className="flex items-center gap-2 text-gray-500 font-medium py-3 px-6 rounded-full border border-gray-200 hover:bg-white transition-colors"
      >
        <Languages size={20} />
        {currentT.lang}
      </button>

      {/* Error Message */}
      <AnimatePresence>
        {error && (
          <motion.div 
            initial={{ opacity: 0, y: 10 }}
            animate={{ opacity: 1, y: 0 }}
            exit={{ opacity: 0 }}
            className="fixed bottom-32 bg-red-500 text-white px-6 py-3 rounded-2xl shadow-lg"
          >
            {currentT.error}
          </motion.div>
        )}
      </AnimatePresence>
    </div>
  );
};

export default LoginPage;
