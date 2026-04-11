import React, { useState } from 'react';
import LoginPage from './pages/Login';
import Dashboard from './pages/Dashboard';
import { LanguageProvider } from './context/LanguageContext';

function App() {
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  return (
    <LanguageProvider>
      <div className="h-screen w-full bg-brand-light font-sans transition-all duration-300">
        {!isAuthenticated ? (
          <LoginPage onLogin={() => setIsAuthenticated(true)} />
        ) : (
          <Dashboard onLogout={() => setIsAuthenticated(false)} />
        )}
      </div>
    </LanguageProvider>
  );
}

export default App;
