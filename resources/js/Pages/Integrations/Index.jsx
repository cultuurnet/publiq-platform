import React from 'react';
import Layout from '../../Shared/Layout';

const Index = ({integrations}) => {
  return (
    <div>
      <h3>Integrations Page</h3>
      <ul>
        {integrations.map((integration) => <li key={integration.id}>{integration.name}</li>)}
      </ul>
    </div>
  )
};

Index.layout = page => <Layout children={page} />;

export default Index;
