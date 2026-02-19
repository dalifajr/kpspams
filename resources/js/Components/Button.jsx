import { forwardRef } from 'react';
import { Link } from '@inertiajs/react';

/**
 * Material You 3 Expressive Button Component
 * Supports: filled, tonal, outlined, text, danger, success variants
 */
const Button = forwardRef(function Button(
    {
        children,
        variant = 'filled',
        size = 'md',
        icon,
        iconPosition = 'left',
        className = '',
        href,
        as,
        type,
        disabled,
        loading,
        fullWidth,
        ...props
    },
    ref
) {
    const variantClasses = {
        filled: 'md-btn md-btn-primary',
        tonal: 'md-btn md-btn-tonal',
        outlined: 'md-btn md-btn-outlined',
        text: 'md-btn md-btn-text',
        danger: 'md-btn md-btn-danger',
        success: 'md-btn md-btn-success',
        secondary: 'md-btn md-btn-secondary',
    };

    const sizeClasses = {
        sm: 'h-8 text-xs px-4',
        md: '',
        lg: 'h-12 text-base px-8',
    };

    const baseClass = variantClasses[variant] || variantClasses.filled;
    const sizeClass = sizeClasses[size] || '';
    const widthClass = fullWidth ? 'w-full' : '';

    const combinedClassName = `${baseClass} ${sizeClass} ${widthClass} ${className}`.trim();

    const content = (
        <>
            {loading && (
                <svg className="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                    <path className="opacity-75" fill="currentColor" d="m4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z" />
                </svg>
            )}
            {icon && iconPosition === 'left' && !loading && (
                <span className="material-symbols-rounded" style={{ fontSize: '20px' }}>{icon}</span>
            )}
            {children && <span>{children}</span>}
            {icon && iconPosition === 'right' && (
                <span className="material-symbols-rounded" style={{ fontSize: '20px' }}>{icon}</span>
            )}
        </>
    );

    if (href) {
        return (
            <Link ref={ref} href={href} className={combinedClassName} {...props}>
                {content}
            </Link>
        );
    }

    const Component = as || 'button';
    const resolvedType = type ?? (Component === 'button' ? 'button' : undefined);

    return (
        <Component
            ref={ref}
            className={combinedClassName}
            type={resolvedType}
            disabled={disabled || loading}
            {...props}
        >
            {content}
        </Component>
    );
});

export default Button;

export function IconButton({
    icon,
    variant = 'standard',
    size = 'md',
    className = '',
    ...props
}) {
    const variantClasses = {
        standard: 'md-icon-btn',
        filled: 'md-icon-btn filled',
        tonal: 'md-icon-btn tonal',
    };

    return (
        <button
            type="button"
            className={`${variantClasses[variant] || variantClasses.standard} ${className}`.trim()}
            {...props}
        >
            <span className="material-symbols-rounded">{icon}</span>
        </button>
    );
}

export function FAB({
    icon,
    label,
    variant = 'primary',
    className = '',
    href,
    ...props
}) {
    const variantClasses = {
        primary: 'md-fab',
        secondary: 'md-fab secondary',
        tertiary: 'md-fab tertiary',
    };

    const baseClass = variantClasses[variant] || variantClasses.primary;
    const extendedClass = label ? 'extended' : '';
    const combinedClassName = `${baseClass} ${extendedClass} ${className}`.trim();

    const content = (
        <>
            <span className="material-symbols-rounded">{icon}</span>
            {label && <span>{label}</span>}
        </>
    );

    if (href) {
        return (
            <Link href={href} className={combinedClassName} {...props}>
                {content}
            </Link>
        );
    }

    return (
        <button type="button" className={combinedClassName} {...props}>
            {content}
        </button>
    );
}

export function FilledButton(props) {
    return <Button variant="filled" {...props} />;
}

export function TonalButton(props) {
    return <Button variant="tonal" {...props} />;
}

export function OutlinedButton(props) {
    return <Button variant="outlined" {...props} />;
}

export function TextButton(props) {
    return <Button variant="text" {...props} />;
}

export function DangerButton(props) {
    return <Button variant="danger" {...props} />;
}
