// Tipos compartilhados dos componentes de Landing

import type { JSX, ReactNode } from 'react';
import { IconType } from 'react-icons';

export type Face = 'front' | 'back' | 'right' | 'left' | 'top' | 'bottom';
export type FaceTransforms = Record<Face, string>;
export type FaceIcons = Record<Face, JSX.Element>;

export interface NavLinkProps {
    href: string;
    children: ReactNode;
}

export interface SkillCardProps {
    title: string;
    icon: IconType;
    items: string[];
}
