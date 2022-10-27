import React from 'react';
import Layout from '../../Shared/Layout';

const Index = ({subscriptions}) => {
  return (
    <div>
      <h3>Subscriptions Page</h3>
      <ul>
        {subscriptions.map((subscription) => <li key={subscription.id}>{subscription.name}</li>)}
      </ul>
    </div>
  )
};

Index.layout = page => <Layout>{page}</Layout>;

export default Index;
