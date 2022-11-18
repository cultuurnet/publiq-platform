import React, { ReactNode } from 'react';
import { Heading } from '../../Shared/Heading';
import Layout from '../../Shared/Layout';

type Props = {
  subscriptions: { id: string; name: string }[];
};

const Index = ({ subscriptions }: Props) => {
  return (
    <div>
      <Heading level={2}>Subscriptions Page</Heading>
      <ul>
        {subscriptions.map((subscription) => (
          <li key={subscription.id}>{subscription.name}</li>
        ))}
      </ul>
    </div>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
