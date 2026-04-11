import React, { useRef, useState, useEffect } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import { X, Camera, RefreshCw } from 'lucide-react';
import { useLanguage } from '../context/LanguageContext';

const CameraOverlay = ({ onCapture, onClose }) => {
  const { t, dir } = useLanguage();
  const videoRef = useRef(null);
  const canvasRef = useRef(null);
  const [stream, setStream] = useState(null);
  const [isProcessing, setIsProcessing] = useState(false);

  useEffect(() => {
    startCamera();
    return () => stopCamera();
  }, []);

  const startCamera = async () => {
    try {
      const s = await navigator.mediaDevices.getUserMedia({
        video: { facingMode: 'environment' },
        audio: false
      });
      setStream(s);
      if (videoRef.current) {
        videoRef.current.srcObject = s;
      }
    } catch (err) {
      console.error("Camera error:", err);
      alert(t('allow_camera'));
      onClose();
    }
  };

  const stopCamera = () => {
    if (stream) {
      stream.getTracks().forEach(track => track.stop());
    }
  };

  const handleCapture = () => {
    if (videoRef.current && canvasRef.current) {
      const context = canvasRef.current.getContext('2d');
      canvasRef.current.width = videoRef.current.videoWidth;
      canvasRef.current.height = videoRef.current.videoHeight;
      context.drawImage(videoRef.current, 0, 0);
      
      canvasRef.current.toBlob((blob) => {
        setIsProcessing(true);
        onCapture(blob); // This will call the AI service
      }, 'image/jpeg', 0.8);
    }
  };

  return (
    <div className={`fixed inset-0 z-50 bg-black flex flex-col`} dir={dir}>
      {/* Viewport */}
      <video 
        ref={videoRef} 
        autoPlay 
        playsInline 
        className="flex-1 object-cover w-full h-full"
      />
      <canvas ref={canvasRef} className="hidden" />

      {/* Top Controls */}
      <div className="absolute top-0 inset-x-0 p-6 flex justify-between items-center bg-gradient-to-b from-black/50 to-transparent">
        <button onClick={onClose} className="text-white p-2">
          <X size={32} />
        </button>
        <div className="text-white font-bold bg-white/20 backdrop-blur-md px-4 py-2 rounded-full border border-white/30">
          {t('camera_status')}
        </div>
      </div>

      {/* Bottom Controls */}
      <div className="absolute bottom-0 inset-x-0 p-12 flex flex-col items-center bg-gradient-to-t from-black/50 to-transparent">
        <AnimatePresence>
          {isProcessing ? (
            <motion.div 
              initial={{ scale: 0.5, opacity: 0 }}
              animate={{ scale: 1, opacity: 1 }}
              className="flex flex-col items-center gap-4 text-white"
            >
              <RefreshCw className="animate-spin" size={48} />
              <span className="text-xl font-bold">{t('ai_analyzing')}</span>
            </motion.div>
          ) : (
            <motion.button
              whileTap={{ scale: 0.9 }}
              onClick={handleCapture}
              className="w-24 h-24 rounded-full bg-white border-8 border-white/30 flex items-center justify-center shadow-2xl"
            >
              <div className="w-16 h-16 rounded-full bg-white border-4 border-gray-200" />
            </motion.button>
          )}
        </AnimatePresence>
      </div>

      {/* Focus Indicator */}
      {!isProcessing && (
        <div className="absolute inset-0 flex items-center justify-center pointer-events-none">
          <div className="w-64 h-64 border-2 border-white/30 rounded-[40px] border-dashed" />
        </div>
      )}
    </div>
  );
};

export default CameraOverlay;
