import { useState, useEffect } from 'react';

/**
 * Material You 3 Alert Component
 */
export default function Alert({ 
    type = 'info', 
    children, 
    onClose, 
    className = '',
    icon,
    title,
    dismissible = true,
    autoClose = 0
}) {
    const [isVisible, setIsVisible] = useState(true);

    useEffect(() => {
        if (autoClose > 0) {
            const timer = setTimeout(() => {
                handleClose();
            }, autoClose);
            return () => clearTimeout(timer);
        }
    }, [autoClose]);

    const icons = {
        success: 'check_circle',
        error: 'error',
        warning: 'warning',
        info: 'info',
    };

    const handleClose = () => {
        setIsVisible(false);
        if (onClose) {
            setTimeout(onClose, 200);
        }
    };

    if (!isVisible) return null;

    return (
        <div className={`md-alert ${type} animate-fade-in ${className}`}>
            <span className="material-symbols-rounded" style={{ flexShrink: 0 }}>
                {icon || icons[type]}
            </span>
            <div className="md-alert__message" style={{ flex: 1 }}>
                {title && <div style={{ fontWeight: 600, marginBottom: '4px' }}>{title}</div>}
                {children}
            </div>
            {dismissible && onClose && (
                <button type="button" className="md-alert__close" onClick={handleClose}>
                    <span className="material-symbols-rounded" style={{ fontSize: '20px' }}>close</span>
                </button>
            )}
        </div>
    );
}

// Convenience Components
export function SuccessAlert({ children, ...props }) {
    return <Alert type="success" {...props}>{children}</Alert>;
}

export function ErrorAlert({ children, ...props }) {
    return <Alert type="error" {...props}>{children}</Alert>;
}

export function WarningAlert({ children, ...props }) {
    return <Alert type="warning" {...props}>{children}</Alert>;
}

export function InfoAlert({ children, ...props }) {
    return <Alert type="info" {...props}>{children}</Alert>;
}

/**
 * Snackbar Component - Material You 3
 */
export function Snackbar({ 
    message, 
    action, 
    onAction, 
    onClose, 
    duration = 4000,
    isOpen 
}) {
    useEffect(() => {
        if (isOpen && duration > 0) {
            const timer = setTimeout(onClose, duration);
            return () => clearTimeout(timer);
        }
    }, [isOpen, duration, onClose]);

    if (!isOpen) return null;

    return (
        <div className="md-snackbar">
            <span style={{ flex: 1 }}>{message}</span>
            {action && (
                <button className="md-snackbar__action" onClick={onAction}>
                    {action}
                </button>
            )}
        </div>
    );
}

/**
 * Toast Hook - for programmatic toasts
 */
export function useToast() {
    const [toasts, setToasts] = useState([]);

    const showToast = (message, type = 'info', duration = 3000) => {
        const id = Date.now();
        setToasts(prev => [...prev, { id, message, type, duration }]);
        
        setTimeout(() => {
            setToasts(prev => prev.filter(t => t.id !== id));
        }, duration);
    };

    const ToastContainer = () => (
        <div style={{ 
            position: 'fixed', 
            top: '16px', 
            right: '16px', 
            zIndex: 200,
            display: 'flex',
            flexDirection: 'column',
            gap: '8px'
        }}>
            {toasts.map(toast => (
                <Alert 
                    key={toast.id} 
                    type={toast.type}
                    onClose={() => setToasts(prev => prev.filter(t => t.id !== toast.id))}
                    autoClose={toast.duration}
                >
                    {toast.message}
                </Alert>
            ))}
        </div>
    );

    return { showToast, ToastContainer };
}
