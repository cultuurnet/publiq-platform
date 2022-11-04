import React from "react";

const Index = ({subscriptions}) => {
  return (
    <div>
      <h1>Subscriptions Page</h1>
      <ul>
        {subscriptions.map((subscription) => <li key={subscription.id}>{subscription.name}</li>)}
      </ul>
    </div>
  )
};

export default Index;
