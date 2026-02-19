import { useEffect, useRef } from 'react';
import Button from './Button';

/**
 * Material You 3 Dialog/Modal Component
 */
export default function Modal({ 
    isOpen, 
    onClose, 
    title, 
    children, 
    footer, 
    size = 'md',
    showCloseButton = true 
}) {
    const dialogRef = useRef(null);

    useEffect(() => {
        const dialog = dialogRef.current;
        if (!dialog) return;

        if (isOpen) {
            if (typeof dialog.showModal === 'function') {
                if (!dialog.open) {
                    dialog.showModal();
                }
            } else {
                dialog.setAttribute('open', '');
            }
            document.body.style.overflow = 'hidden';
        } else {
            if (typeof dialog.close === 'function' && dialog.open) {
                dialog.close();
            } else {
                dialog.removeAttribute('open');
            }
            document.body.style.overflow = '';
        }

        return () => {
            document.body.style.overflow = '';
        };
    }, [isOpen]);

    // Don't render anything if not open
    if (!isOpen) return null;

    const handleBackdropClick = (e) => {
        if (e.target === dialogRef.current) {
            onClose();
        }
    };

    const sizeClasses = {
        sm: 'max-w-sm',
        md: 'max-w-lg',
        lg: 'max-w-2xl',
        xl: 'max-w-4xl',
        full: 'max-w-full mx-4',
    };

    return (
        <dialog
            ref={dialogRef}
            className="md-dialog"
            onClick={handleBackdropClick}
            onClose={onClose}
        >
            <div className={`md-dialog__surface ${sizeClasses[size] || sizeClasses.md}`}>
                {title && (
                    <div className="md-dialog__header">
                        <h2 className="md-dialog__title">{title}</h2>
                        {showCloseButton && (
                            <button 
                                type="button" 
                                className="md-icon-btn" 
                                onClick={onClose}
                                style={{ marginRight: '-8px' }}
                            >
                                <span className="material-symbols-rounded">close</span>
                            </button>
                        )}
                    </div>
                )}
                <div className="md-dialog__body">{children}</div>
                {footer && <div className="md-dialog__footer">{footer}</div>}
            </div>
        </dialog>
    );
}

/**
 * Confirm Dialog - Pre-styled confirmation modal
 */
export function ConfirmModal({ 
    isOpen, 
    onClose, 
    onConfirm, 
    title = 'Konfirmasi', 
    message, 
    confirmText = 'Konfirmasi', 
    cancelText = 'Batal', 
    variant = 'danger',
    loading = false,
    confirmDisabled = false
}) {
    const handleConfirm = () => {
        if (confirmDisabled) return;
        if (onConfirm) onConfirm();
    };

    return (
        <Modal
            isOpen={isOpen}
            onClose={onClose}
            title={title}
            size="sm"
            footer={
                <>
                    <Button variant="text" onClick={onClose} disabled={loading}>
                        {cancelText}
                    </Button>
                    <Button 
                        variant={variant} 
                        onClick={handleConfirm} 
                        loading={loading}
                        disabled={confirmDisabled}
                    >
                        {confirmText}
                    </Button>
                </>
            }
        >
            <p style={{ margin: 0, color: 'var(--md-sys-color-on-surface-variant)' }}>{message}</p>
        </Modal>
    );
}

/**
 * Alert Dialog - Simple alert with OK button
 */
export function AlertModal({
    isOpen,
    onClose,
    title = 'Informasi',
    message,
    buttonText = 'OK'
}) {
    return (
        <Modal
            isOpen={isOpen}
            onClose={onClose}
            title={title}
            size="sm"
            showCloseButton={false}
            footer={
                <Button variant="filled" onClick={onClose}>
                    {buttonText}
                </Button>
            }
        >
            <p style={{ margin: 0, color: 'var(--md-sys-color-on-surface-variant)' }}>{message}</p>
        </Modal>
    );
}

/**
 * Bottom Sheet Modal - Slides from bottom (mobile-friendly)
 */
export function BottomSheet({
    isOpen,
    onClose,
    title,
    children,
}) {
    const sheetRef = useRef(null);

    useEffect(() => {
        if (isOpen) {
            document.body.style.overflow = 'hidden';
        } else {
            document.body.style.overflow = '';
        }

        return () => {
            document.body.style.overflow = '';
        };
    }, [isOpen]);

    if (!isOpen) return null;

    return (
        <div 
            className="fixed inset-0" 
            onClick={onClose}
            style={{ background: 'rgba(0,0,0,0.4)', zIndex: 220 }}
        >
            <div 
                ref={sheetRef}
                className="fixed left-0 right-0 bottom-0 bg-surface rounded-t-3xl animate-slide-up"
                onClick={(e) => e.stopPropagation()}
                style={{ 
                    background: 'var(--md-sys-color-surface-container-low)',
                    borderRadius: '28px 28px 0 0',
                    maxHeight: '90vh',
                    overflow: 'auto',
                    paddingBottom: 'env(safe-area-inset-bottom, 0)'
                }}
            >
                <div style={{ 
                    width: '32px', 
                    height: '4px', 
                    background: 'var(--md-sys-color-outline-variant)',
                    borderRadius: '2px',
                    margin: '12px auto'
                }} />
                {title && (
                    <div style={{ padding: '0 24px 16px', borderBottom: '1px solid var(--md-sys-color-outline-variant)' }}>
                        <h2 className="md-title-large" style={{ margin: 0 }}>{title}</h2>
                    </div>
                )}
                <div style={{ padding: '16px 24px 24px' }}>
                    {children}
                </div>
            </div>
        </div>
    );
}
