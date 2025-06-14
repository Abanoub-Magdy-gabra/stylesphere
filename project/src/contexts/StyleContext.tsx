import { createContext, useContext, useState, useEffect, ReactNode } from 'react';

interface StylePreferences {
  colors: string[];
  patterns: string[];
  fits: string[];
  occasions: string[];
  favorites: string[];
  bodyType?: string;
  size?: string;
  stylePersona?: string;
}

interface StyleContextType {
  preferences: StylePreferences;
  updatePreferences: (newPreferences: Partial<StylePreferences>) => void;
  resetPreferences: () => void;
  hasCompletedStyleQuiz: boolean;
  setHasCompletedStyleQuiz: (value: boolean) => void;
}

const defaultPreferences: StylePreferences = {
  colors: [],
  patterns: [],
  fits: [],
  occasions: [],
  favorites: [],
};

const StyleContext = createContext<StyleContextType | undefined>(undefined);

export const StyleProvider = ({ children }: { children: ReactNode }) => {
  const [preferences, setPreferences] = useState<StylePreferences>(defaultPreferences);
  const [hasCompletedStyleQuiz, setHasCompletedStyleQuiz] = useState(false);

  // Load preferences from localStorage on component mount
  useEffect(() => {
    const savedPreferences = localStorage.getItem('stylePreferences');
    if (savedPreferences) {
      setPreferences(JSON.parse(savedPreferences));
    }
    
    const quizCompleted = localStorage.getItem('styleQuizCompleted') === 'true';
    setHasCompletedStyleQuiz(quizCompleted);
  }, []);

  // Save preferences to localStorage whenever they change
  useEffect(() => {
    localStorage.setItem('stylePreferences', JSON.stringify(preferences));
  }, [preferences]);

  // Save quiz completion status
  useEffect(() => {
    localStorage.setItem('styleQuizCompleted', hasCompletedStyleQuiz.toString());
  }, [hasCompletedStyleQuiz]);

  const updatePreferences = (newPreferences: Partial<StylePreferences>) => {
    setPreferences(prev => ({ ...prev, ...newPreferences }));
  };

  const resetPreferences = () => {
    setPreferences(defaultPreferences);
    setHasCompletedStyleQuiz(false);
  };

  return (
    <StyleContext.Provider value={{
      preferences,
      updatePreferences,
      resetPreferences,
      hasCompletedStyleQuiz,
      setHasCompletedStyleQuiz
    }}>
      {children}
    </StyleContext.Provider>
  );
};

export const useStyle = () => {
  const context = useContext(StyleContext);
  if (context === undefined) {
    throw new Error('useStyle must be used within a StyleProvider');
  }
  return context;
};