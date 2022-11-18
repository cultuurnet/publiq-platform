import React, { FormEvent, ReactNode } from 'react';
import { useForm } from '@inertiajs/inertia-react';
import Layout from '../../Shared/Layout';
import { Heading } from '../../Shared/Heading';

type Props = {
  integrationTypes: string[];
  subscriptions: { id: string; name: string }[];
};

const Index = ({ integrationTypes, subscriptions }: Props) => {
  const { data, setData, errors, post, processing } = useForm({
    integrationType: '',
    subscriptionId: '',
    name: '',
    description: '',
    firstNameOrganisation: '',
    lastNameOrganisation: '',
    emailOrganisation: '',
    firstNamePartner: '',
    lastNamePartner: '',
    emailPartner: '',
  });

  function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    post('/integrations');
  }

  return (
    <div>
      <Heading level={2}>Integratie toevoegen</Heading>

      <form onSubmit={handleSubmit}>
        <h4>Type integratie</h4>
        <select
          name="integrationType"
          id="integrationType"
          value={data.integrationType}
          onChange={(e) => setData('integrationType', e.target.value)}
        >
          <option value="">Kies...</option>
          {integrationTypes.map((integrationType) => (
            <option value={integrationType} key={integrationType}>
              {integrationType}
            </option>
          ))}
        </select>
        {errors.integrationType && (
          <div className="error">{errors.integrationType}</div>
        )}

        <h4>Plan</h4>
        <select
          name="subscriptionId"
          id="subscriptionId"
          value={data.subscriptionId}
          onChange={(e) => setData('subscriptionId', e.target.value)}
        >
          <option value="">Kies...</option>
          {subscriptions.map((subscription) => (
            <option value={subscription.id} key={subscription.id}>
              {subscription.name}
            </option>
          ))}
        </select>
        {errors.subscriptionId && (
          <div className="error">{errors.subscriptionId}</div>
        )}

        <h4>Naam integratie</h4>
        <input
          type="text"
          name="name"
          id="name"
          value={data.name}
          onChange={(e) => setData('name', e.target.value)}
        />
        {errors.name && <div className="error">{errors.name}</div>}

        <h4>Doel van de integratie</h4>
        <input
          type="text"
          name="description"
          id="description"
          value={data.description}
          onChange={(e) => setData('description', e.target.value)}
        />
        {errors.description && (
          <div className="error">{errors.description}</div>
        )}

        <h4>Contact organisatie</h4>
        <input
          type="text"
          name="firstNameOrganisation"
          id="firstNameOrganisation"
          value={data.firstNameOrganisation}
          onChange={(e) => setData('firstNameOrganisation', e.target.value)}
          placeholder="Voornaam"
        />
        {errors.firstNameOrganisation && (
          <div className="error">{errors.firstNameOrganisation}</div>
        )}

        <input
          type="text"
          name="lastNameOrganisation"
          id="lastNameOrganisation"
          value={data.lastNameOrganisation}
          onChange={(e) => setData('lastNameOrganisation', e.target.value)}
          placeholder="Achternaam"
        />
        {errors.lastNameOrganisation && (
          <div className="error">{errors.lastNameOrganisation}</div>
        )}

        <input
          type="email"
          name="emailOrganisation"
          id="emailOrganisation"
          value={data.emailOrganisation}
          onChange={(e) => setData('emailOrganisation', e.target.value)}
          placeholder="Emailadres"
        />
        {errors.emailOrganisation && (
          <div className="error">{errors.emailOrganisation}</div>
        )}

        <h4>Contact technische partner</h4>
        <input
          type="text"
          name="firstNamePartner"
          id="firstNamePartner"
          value={data.firstNamePartner}
          onChange={(e) => setData('firstNamePartner', e.target.value)}
          placeholder="Voornaam"
        />
        {errors.firstNamePartner && (
          <div className="error">{errors.firstNamePartner}</div>
        )}

        <input
          type="text"
          name="lastNamePartner"
          id="lastNamePartner"
          value={data.lastNamePartner}
          onChange={(e) => setData('lastNamePartner', e.target.value)}
          placeholder="Achternaam"
        />
        {errors.lastNamePartner && (
          <div className="error">{errors.lastNamePartner}</div>
        )}

        <input
          type="email"
          name="emailPartner"
          id="emailPartner"
          value={data.emailPartner}
          onChange={(e) => setData('emailPartner', e.target.value)}
          placeholder="Emailadres"
        />
        {errors.emailPartner && (
          <div className="error">{errors.emailPartner}</div>
        )}

        <h4>Gebruiksvoorwaarden</h4>
        <input type="checkbox" id="agreement" name="agreement" required />
        <label htmlFor="agreement">Ik ga akkoord</label>

        <br />
        <button type="submit" disabled={processing}>
          Integratie aanmaken
        </button>
      </form>
    </div>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
