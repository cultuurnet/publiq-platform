import React from "react";
import { Hero } from "../Components/Hero";
import { IntegrationTypes } from "../Components/IntegrationTypes";
import { Page } from "../Components/Page";
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

export default Index;
