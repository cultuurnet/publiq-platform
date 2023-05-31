import React, { ReactNode } from "react";
import { Heading } from "../Shared/Heading";
import Layout from "../Shared/Layout";
import { useTranslation } from "react-i18next";

const Index = () => {
  const { t } = useTranslation();

  return (
    <section className="flex flex-col items-center">
      <div className="w-full bg-red-100 h-44"></div>
      <div className="flex flex-col items-center py-8 px-12">
        <button className="flex flex-col items-center py-8 px-12"></button>
      <Heading level={2}>{t('home.hero.title')}</Heading>
      <p className="text-center">{t('home.hero.intro')}</p>
      <button>{t('home.hero.start_here')}</button>
      </div>
    </section>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
