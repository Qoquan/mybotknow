export type Appearance = 'light' | 'dark' | 'epic' | 'system';
export type ResolvedAppearance = 'light' | 'dark' | 'epic';

export type AppVariant = 'header' | 'sidebar';

export type FlashToast = {
    type: 'success' | 'info' | 'warning' | 'error';
    message: string;
};
