import React, { ReactNode } from 'react';
import { Heading } from '../Shared/Heading';
import Layout from '../Shared/Layout';

const Index = () => {
  return (
    <div>
      <Heading level={2}>Index Page</Heading>
    </div>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
