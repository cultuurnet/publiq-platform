import React, { ReactNode } from 'react';
import Layout from '../Shared/Layout';

const Index = () => {
  return (
    <div>
      <h3>Index Page</h3>
    </div>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
