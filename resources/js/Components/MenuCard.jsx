import { Link } from '@inertiajs/react';

/**
 * Material You 3 Menu Card Component
 * Used in dashboard for navigation tiles
 */
export default function MenuCard({ menu, number }) {
    // Generate a tonal color based on the menu color
    const getIconStyle = (color) => {
        if (!color) {
            return {
                backgroundColor: 'var(--md-sys-color-primary-container)',
                color: 'var(--md-sys-color-on-primary-container)',
            };
        }
        
        // Create a light tinted background
        return {
            backgroundColor: `${color}20`,
            color: color,
        };
    };

    return (
        <Link href={menu.url} className="md-menu-card">
            <div className="md-menu-card__icon" style={getIconStyle(menu.color)}>
                <span className="material-symbols-rounded">{menu.icon}</span>
            </div>
            <div className="md-menu-card__label">{menu.label}</div>
            {number && <div className="md-menu-card__number">#{number}</div>}
        </Link>
    );
}

/**
 * Large Menu Card - For featured items
 */
export function LargeMenuCard({ menu, subtitle, stats }) {
    const getIconStyle = (color) => {
        if (!color) {
            return {
                backgroundColor: 'var(--md-sys-color-primary-container)',
                color: 'var(--md-sys-color-on-primary-container)',
            };
        }
        
        return {
            backgroundColor: `${color}20`,
            color: color,
        };
    };

    return (
        <Link 
            href={menu.url} 
            className="md-card" 
            style={{ 
                display: 'flex', 
                alignItems: 'center', 
                gap: '16px',
                textDecoration: 'none',
                color: 'inherit'
            }}
        >
            <div 
                style={{
                    width: '56px',
                    height: '56px',
                    borderRadius: 'var(--shape-expressive-medium)',
                    display: 'flex',
                    alignItems: 'center',
                    justifyContent: 'center',
                    ...getIconStyle(menu.color)
                }}
            >
                <span className="material-symbols-rounded" style={{ fontSize: '28px' }}>{menu.icon}</span>
            </div>
            <div style={{ flex: 1 }}>
                <div className="md-title-medium" style={{ marginBottom: '2px' }}>{menu.label}</div>
                {subtitle && (
                    <div className="md-body-small text-muted">{subtitle}</div>
                )}
                {stats && (
                    <div style={{ display: 'flex', gap: '16px', marginTop: '8px' }}>
                        {stats.map((stat, i) => (
                            <div key={i} className="md-label-small text-muted">
                                <span style={{ fontWeight: 600, color: 'var(--md-sys-color-on-surface)' }}>{stat.value}</span> {stat.label}
                            </div>
                        ))}
                    </div>
                )}
            </div>
            <span className="material-symbols-rounded" style={{ color: 'var(--md-sys-color-outline)' }}>
                chevron_right
            </span>
        </Link>
    );
}

/**
 * Compact Menu Item - For lists
 */
export function MenuListItem({ menu, trailing }) {
    return (
        <Link href={menu.url} className="md-list-item">
            <div 
                className="md-list-item__leading" 
                style={{
                    backgroundColor: menu.color ? `${menu.color}20` : 'var(--md-sys-color-primary-container)',
                    color: menu.color || 'var(--md-sys-color-on-primary-container)',
                }}
            >
                <span className="material-symbols-rounded" style={{ fontSize: '20px' }}>{menu.icon}</span>
            </div>
            <div className="md-list-item__content">
                <div className="md-list-item__headline">{menu.label}</div>
            </div>
            {trailing || (
                <span className="material-symbols-rounded md-list-item__trailing">chevron_right</span>
            )}
        </Link>
    );
}
