import React, { ReactNode } from 'react';
import Layout from '../../Shared/Layout';

type Props = {
  integrations: { id: string; name: string }[];
};

const Index = ({ integrations }: Props) => {
  return (
    <div>
      <h3>Integrations Page</h3>
      <ul>
        {integrations.map((integration) => (
          <li key={integration.id}>{integration.name}</li>
        ))}
      </ul>
    </div>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
