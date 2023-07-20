import React, { ReactNode } from "react";
import Layout from "../Components/Layout";
import { Hero } from "../Components/Hero";
import { IntegrationTypes } from "../Components/IntegrationTypes";

const Index = () => {
  return (
    <>
      <div className="w-full bg-red-100 h-44"></div>
      <div className="px-6">
        <Hero />
        <IntegrationTypes />
      </div>
    </>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
