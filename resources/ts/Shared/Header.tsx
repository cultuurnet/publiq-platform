import React from 'react';
import { Heading } from './Heading';
import { useTranslation } from 'react-i18next';

export default function Header() {
  const { t } = useTranslation();
  return (
    <header>
      <Heading level={1}>{t('title')}</Heading>
    </header>
  );
}
