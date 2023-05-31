import React, { ReactNode } from "react";
import { Heading } from "../Shared/Heading";
import Layout from "../Shared/Layout";
import { useTranslation } from "react-i18next";

const Index = () => {
  return (
    <>
      <Hero />
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
