import React, { ReactNode } from "react";
import Layout from "../Shared/Layout";
import { Hero } from "../Shared/Hero";
import { IntegrationTypes } from "../Shared/IntegrationTypes";

const Index = () => {
  return (
    <>
      <Hero />
      <IntegrationTypes />
    </>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
