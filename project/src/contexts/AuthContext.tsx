import { createContext, useContext, useState, useEffect, ReactNode } from 'react';
import toast from 'react-hot-toast';

interface User {
  id: string;
  name: string;
  email: string;
  avatar?: string;
}

interface AuthContextType {
  user: User | null;
  isAuthenticated: boolean;
  login: (email: string, password: string) => Promise<boolean>;
  logout: () => void;
  register: (name: string, email: string, password: string) => Promise<boolean>;
}

const AuthContext = createContext<AuthContextType | undefined>(undefined);

export const AuthProvider = ({ children }: { children: ReactNode }) => {
  const [user, setUser] = useState<User | null>(null);
  const [isAuthenticated, setIsAuthenticated] = useState(false);

  // Check if user is already logged in
  useEffect(() => {
    const savedUser = localStorage.getItem('user');
    if (savedUser) {
      setUser(JSON.parse(savedUser));
      setIsAuthenticated(true);
    }
  }, []);

  const login = async (email: string, password: string): Promise<boolean> => {
    try {
      // This is a mock login. In a real app, this would call an API
      // For demo purposes, we'll accept any email with a valid format and any password
      if (!email.includes('@')) {
        toast.error('Invalid email format');
        return false;
      }

      const mockUser: User = {
        id: '1',
        name: email.split('@')[0],
        email,
        avatar: 'https://i.pravatar.cc/150?u=' + email,
      };

      setUser(mockUser);
      setIsAuthenticated(true);
      localStorage.setItem('user', JSON.stringify(mockUser));
      toast.success('Logged in successfully');
      return true;
    } catch (error) {
      toast.error('Login failed');
      return false;
    }
  };

  const logout = () => {
    setUser(null);
    setIsAuthenticated(false);
    localStorage.removeItem('user');
    toast.success('Logged out successfully');
  };

  const register = async (name: string, email: string, password: string): Promise<boolean> => {
    try {
      // This is a mock registration. In a real app, this would call an API
      if (!email.includes('@')) {
        toast.error('Invalid email format');
        return false;
      }

      if (password.length < 6) {
        toast.error('Password must be at least 6 characters');
        return false;
      }

      const mockUser: User = {
        id: '1',
        name,
        email,
        avatar: 'https://i.pravatar.cc/150?u=' + email,
      };

      setUser(mockUser);
      setIsAuthenticated(true);
      localStorage.setItem('user', JSON.stringify(mockUser));
      toast.success('Account created successfully');
      return true;
    } catch (error) {
      toast.error('Registration failed');
      return false;
    }
  };

  return (
    <AuthContext.Provider value={{
      user,
      isAuthenticated,
      login,
      logout,
      register
    }}>
      {children}
    </AuthContext.Provider>
  );
};

export const useAuth = () => {
  const context = useContext(AuthContext);
  if (context === undefined) {
    throw new Error('useAuth must be used within an AuthProvider');
  }
  return context;
};