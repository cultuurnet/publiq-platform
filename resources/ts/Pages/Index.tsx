import React, { ReactNode } from "react";
import { Hero } from "../Components/Hero";
import { IntegrationTypes } from "../Components/IntegrationTypes";
import { Page } from "../Components/Page";
import HomePageLayout from "../layouts/HomePageLayout";

const Index = () => {
  return (
    <>
      <div className="w-full bg-gray-600 h-44"></div>
      <Page>
        <Hero />
        <IntegrationTypes />
      </Page>
    </>
  );
};

Index.layout = (page: ReactNode) => <HomePageLayout>{page}</HomePageLayout>;

export default Index;
