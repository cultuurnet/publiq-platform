import type { ReactNode } from "react";
import React from "react";
import { Hero } from "../Components/Hero";
import { IntegrationTypes } from "../Components/IntegrationTypes";
import { Page } from "../Components/Page";
import HomePageLayout from "../layouts/HomePageLayout";
import { HeroImage } from "../Components/HeroImage";

const Index = () => {
  return (
    <>
      <HeroImage />
      <Page>
        <Hero />
        <IntegrationTypes />
      </Page>
    </>
  );
};

Index.layout = (page: ReactNode) => <HomePageLayout>{page}</HomePageLayout>;

export default Index;
