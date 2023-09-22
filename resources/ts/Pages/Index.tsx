import React, { ReactNode } from "react";
import Layout from "../Components/Layout";
import { Hero } from "../Components/Hero";
import { IntegrationTypes } from "../Components/IntegrationTypes";
import { Page } from "../Components/Page";

const Index = () => {
  return (
    <>
      <div className="w-full bg-red-100 h-44"></div>
      <Page>
        <Hero />
        <IntegrationTypes />
      </Page>
    </>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
