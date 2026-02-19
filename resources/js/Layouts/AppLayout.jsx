import { usePage } from '@inertiajs/react';
import BottomNav from '@/Components/BottomNav';

/**
 * Main Application Layout
 * Provides consistent structure with bottom navigation
 */
export default function AppLayout({ children, showNav = true }) {
    const { auth } = usePage().props;

    return (
        <div className="app-wrapper">
            {children}
            {showNav && auth?.user && <BottomNav />}
        </div>
    );
}

/**
 * Page Container - Provides consistent page structure
 */
export function PageContainer({ children, className = '' }) {
    return (
        <div className={`md-screen ${className}`}>
            {children}
        </div>
    );
}

/**
 * Top App Bar Component
 */
export function TopAppBar({ 
    title, 
    backHref, 
    onBack,
    actions,
    variant = 'default',
    subtitle
}) {
    const handleBack = () => {
        if (onBack) {
            onBack();
        } else if (backHref) {
            window.location.href = backHref;
        } else {
            window.history.back();
        }
    };

    const variantStyles = {
        default: {},
        transparent: { 
            background: 'transparent', 
            backdropFilter: 'none' 
        },
        colored: {
            background: 'var(--md-sys-color-primary)',
            color: 'var(--md-sys-color-on-primary)'
        }
    };

    return (
        <header className="md-top-app-bar" style={variantStyles[variant]}>
            <div className="md-top-app-bar__content">
                {(backHref || onBack) && (
                    <button 
                        type="button" 
                        className="md-icon-btn" 
                        onClick={handleBack}
                        style={{ marginLeft: '-8px' }}
                    >
                        <span className="material-symbols-rounded">arrow_back</span>
                    </button>
                )}
                
                <div style={{ flex: 1, marginLeft: backHref || onBack ? '4px' : '8px' }}>
                    <span className="md-title-large">{title}</span>
                    {subtitle && (
                        <div className="md-body-small text-muted">{subtitle}</div>
                    )}
                </div>
                
                {actions && (
                    <div style={{ display: 'flex', gap: '4px' }}>
                        {actions}
                    </div>
                )}
            </div>
        </header>
    );
}

/**
 * Page Content - Main content area with padding
 */
export function PageContent({ children, className = '', noPadding = false }) {
    return (
        <div 
            className={className}
            style={{ 
                padding: noPadding ? 0 : '0 16px',
                paddingBottom: '24px'
            }}
        >
            {children}
        </div>
    );
}

/**
 * Section Component - For grouping content
 */
export function Section({ title, children, action, className = '', style = {} }) {
    return (
        <section className={className} style={{ marginBottom: '24px', ...style }}>
            {(title || action) && (
                <div className="md-section-title">
                    {title && <span>{title}</span>}
                    {action && <div className="md-section-action">{action}</div>}
                </div>
            )}
            {children}
        </section>
    );
}

/**
 * Empty State Component
 */
export function EmptyState({ icon = 'inbox', title, message, action }) {
    return (
        <div className="md-empty-state">
            <span className="material-symbols-rounded">{icon}</span>
            {title && <h3>{title}</h3>}
            {message && <p>{message}</p>}
            {action && <div style={{ marginTop: '16px' }}>{action}</div>}
        </div>
    );
}

/**
 * Loading State Component
 */
export function LoadingState({ message = 'Memuat...' }) {
    return (
        <div style={{ 
            display: 'flex', 
            flexDirection: 'column',
            alignItems: 'center', 
            justifyContent: 'center', 
            padding: '48px',
            gap: '16px'
        }}>
            <svg className="md-progress-circular" viewBox="0 0 50 50">
                <circle cx="25" cy="25" r="20" />
            </svg>
            <span className="md-body-medium text-muted">{message}</span>
        </div>
    );
}
