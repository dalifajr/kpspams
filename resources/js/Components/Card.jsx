/**
 * Material You 3 Card Components
 */

export default function Card({ children, variant = 'elevated', className = '', onClick, ...props }) {
    const variantClasses = {
        elevated: 'md-card',
        filled: 'md-card-filled',
        outlined: 'md-card-outlined',
    };

    const baseClass = variantClasses[variant] || variantClasses.elevated;
    const clickableClass = onClick ? 'cursor-pointer' : '';

    return (
        <div 
            className={`${baseClass} ${clickableClass} ${className}`.trim()} 
            onClick={onClick}
            {...props}
        >
            {children}
        </div>
    );
}

export function CardHeader({ children, className = '', actions, title, subtitle }) {
    return (
        <div className={`flex items-start justify-between mb-4 ${className}`}>
            <div>
                {title && <h3 className="md-title-medium" style={{ margin: 0 }}>{title}</h3>}
                {subtitle && <p className="md-body-medium text-muted" style={{ margin: '4px 0 0' }}>{subtitle}</p>}
                {children}
            </div>
            {actions && <div className="flex gap-2">{actions}</div>}
        </div>
    );
}

export function CardBody({ children, className = '' }) {
    return <div className={className}>{children}</div>;
}

export function CardFooter({ children, className = '' }) {
    return (
        <div className={`flex justify-end gap-2 mt-4 pt-4 ${className}`} style={{ borderTop: '1px solid var(--md-sys-color-outline-variant)' }}>
            {children}
        </div>
    );
}

/**
 * Hero Card - For dashboard headers
 */
export function HeroCard({ children, className = '' }) {
    return (
        <div className={`md-hero-card ${className}`}>
            {children}
        </div>
    );
}

/**
 * Stat Card - For displaying statistics
 */
export function StatCard({ label, value, icon, color, trend, className = '' }) {
    return (
        <div className={`md-card-filled ${className}`} style={{ textAlign: 'center', padding: '20px' }}>
            {icon && (
                <div 
                    style={{ 
                        width: '48px', 
                        height: '48px', 
                        borderRadius: '16px',
                        background: color ? `${color}20` : 'var(--md-sys-color-primary-container)',
                        color: color || 'var(--md-sys-color-on-primary-container)',
                        display: 'flex',
                        alignItems: 'center',
                        justifyContent: 'center',
                        margin: '0 auto 12px'
                    }}
                >
                    <span className="material-symbols-rounded">{icon}</span>
                </div>
            )}
            <div className="md-display-small" style={{ fontWeight: 500, fontSize: '2rem' }}>{value}</div>
            <div className="md-label-medium text-muted" style={{ marginTop: '4px' }}>{label}</div>
            {trend && (
                <div style={{ 
                    marginTop: '8px', 
                    fontSize: '0.75rem', 
                    color: trend > 0 ? 'var(--md-sys-color-success)' : 'var(--md-sys-color-error)' 
                }}>
                    {trend > 0 ? '↑' : '↓'} {Math.abs(trend)}%
                </div>
            )}
        </div>
    );
}

/**
 * List Card - Card with list items inside
 */
export function ListCard({ title, children, className = '', action }) {
    return (
        <Card variant="outlined" className={className}>
            {(title || action) && (
                <div className="flex justify-between items-center mb-3">
                    {title && <h3 className="md-title-medium" style={{ margin: 0 }}>{title}</h3>}
                    {action}
                </div>
            )}
            <div style={{ margin: '-16px', marginTop: '0' }}>
                {children}
            </div>
        </Card>
    );
}
