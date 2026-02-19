import { forwardRef, useState } from 'react';

/**
 * Material You 3 Text Field Component
 */
const Input = forwardRef(function Input(
    { label, icon, type = 'text', error, helperText, className = '', variant = 'filled', ...props },
    ref
) {
    const [showPassword, setShowPassword] = useState(false);
    const isPassword = type === 'password';
    const inputType = isPassword && showPassword ? 'text' : type;

    const fieldClass = variant === 'outlined' ? 'md-text-field-outlined' : 'md-text-field';
    const errorClass = error ? 'error' : '';

    return (
        <div className={`${className}`}>
            <div className={`${fieldClass} ${errorClass}`}>
                {label && <label>{label}</label>}
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    {icon && <span className="material-symbols-rounded" style={{ color: 'var(--md-sys-color-on-surface-variant)', fontSize: '20px' }}>{icon}</span>}
                    <input ref={ref} type={inputType} {...props} style={{ flex: 1 }} />
                    {isPassword && (
                        <button
                            type="button"
                            onClick={() => setShowPassword(!showPassword)}
                            style={{ background: 'none', border: 'none', padding: '4px', cursor: 'pointer', color: 'var(--md-sys-color-on-surface-variant)' }}
                        >
                            <span className="material-symbols-rounded" style={{ fontSize: '20px' }}>
                                {showPassword ? 'visibility_off' : 'visibility'}
                            </span>
                        </button>
                    )}
                </div>
            </div>
            {error && <p className="form-error" style={{ marginTop: '4px', marginLeft: '16px' }}>{error}</p>}
            {helperText && !error && (
                <p style={{ fontSize: '0.75rem', color: 'var(--md-sys-color-on-surface-variant)', marginTop: '4px', marginLeft: '16px' }}>
                    {helperText}
                </p>
            )}
        </div>
    );
});

export default Input;

/**
 * Material You 3 Select Component
 */
export function Select({ label, icon, error, children, className = '', ...props }) {
    return (
        <div className={className}>
            <div className={`md-text-field ${error ? 'error' : ''}`}>
                {label && <label>{label}</label>}
                <div style={{ display: 'flex', alignItems: 'center', gap: '8px' }}>
                    {icon && <span className="material-symbols-rounded" style={{ color: 'var(--md-sys-color-on-surface-variant)', fontSize: '20px' }}>{icon}</span>}
                    <select {...props} style={{ flex: 1, background: 'transparent', border: 'none', outline: 'none', fontSize: '1rem', color: 'var(--md-sys-color-on-surface)' }}>
                        {children}
                    </select>
                </div>
            </div>
            {error && <p className="form-error" style={{ marginTop: '4px', marginLeft: '16px' }}>{error}</p>}
        </div>
    );
}

/**
 * Material You 3 Textarea Component
 */
export function Textarea({ label, error, className = '', rows = 4, ...props }) {
    return (
        <div className={className}>
            <div className={`md-text-field ${error ? 'error' : ''}`}>
                {label && <label>{label}</label>}
                <textarea
                    {...props}
                    rows={rows}
                    style={{
                        width: '100%',
                        resize: 'vertical',
                        minHeight: '80px',
                        fontFamily: 'inherit',
                    }}
                />
            </div>
            {error && <p className="form-error" style={{ marginTop: '4px', marginLeft: '16px' }}>{error}</p>}
        </div>
    );
}

/**
 * Search Bar Component - Material You 3 Style
 */
export function SearchBar({ value, onChange, onSearch, placeholder = 'Search...', className = '' }) {
    const handleKeyDown = (e) => {
        if (e.key === 'Enter' && onSearch) {
            onSearch(value);
        }
    };

    return (
        <div className={`md-search-bar ${className}`}>
            <span className="material-symbols-rounded">search</span>
            <input
                type="text"
                value={value}
                onChange={(e) => onChange(e.target.value)}
                onKeyDown={handleKeyDown}
                placeholder={placeholder}
            />
            {value && (
                <button
                    type="button"
                    onClick={() => onChange('')}
                    style={{ background: 'none', border: 'none', padding: '4px', cursor: 'pointer', color: 'var(--md-sys-color-on-surface-variant)' }}
                >
                    <span className="material-symbols-rounded" style={{ fontSize: '20px' }}>close</span>
                </button>
            )}
        </div>
    );
}
