import React from 'react';
import Layout from '../../Shared/Layout';

const Index = ({integrationTypes, subscriptions}) => {
  return (
    <div>
      <h2>Integratie toevoegen</h2>

      <h4>Type integratie</h4>
      <select name="integrationType" id="integrationType">
        {integrationTypes.map((integrationType) => <option value={integrationType} key={integrationType}>{integrationType}</option>)}
      </select>

      <h4>Plan</h4>
      <select name="subscription" id="subscription">
        {subscriptions.map((subscription) => <option value={subscription.id} key={subscription.id}>{subscription.name}</option>)}
      </select>

      <h4>Naam integratie</h4>
      <input type="text" name="name" id="name" />

      <h4>Doel van de integratie</h4>
      <input type="text" name="description" id="description" />

      <h4>Contact organisatie</h4>
      <input type="text" name="firstNameOrganisation" id="firstNameOrganisation" placeholder="Voornaam" />
      <input type="text" name="lastNameOrganisation" id="lastNameOrganisation" placeholder="Achternaam" />
      <input type="email" name="emailOrganisation" id="emailOrganisation" placeholder="Emailadres" />

      <h4>Contact technische partner</h4>
      <input type="text" name="firstNamePartner" id="firstNamePartner" placeholder="Voornaam" />
      <input type="text" name="lastNamePartner" id="lastNamePartner" placeholder="Achternaam" />
      <input type="email" name="emailPartner" id="emailPartner" placeholder="Emailadres" />
      
      <h4>Gebruiksvoorwaarden</h4>
      <input type="checkbox" id="agreement" name="agreement" />
      <label htmlFor="agreement">Ik ga akkoord</label>

      <br />
      <button type="submit">Integratie aanmaken</button>

    </div>
  )
};

Index.layout = page => <Layout children={page} />;

export default Index;
