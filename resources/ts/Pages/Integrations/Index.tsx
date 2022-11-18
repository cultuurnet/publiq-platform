import React, { ReactNode } from 'react';
import { Heading } from '../../Shared/Heading';
import Layout from '../../Shared/Layout';

type Props = {
  integrations: { id: string; name: string }[];
};

const Index = ({ integrations }: Props) => {
  return (
    <div>
      <Heading level={2}>Integrations Page</Heading>
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
