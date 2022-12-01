import React, { FormEvent, ReactNode } from 'react';
import { useForm } from '@inertiajs/inertia-react';
import Layout from '../../Shared/Layout';
import { Heading } from '../../Shared/Heading';
import { FormElement } from '../../Shared/FormElement';

type Props = {
  integrationTypes: string[];
  subscriptions: { id: string; name: string }[];
};

const initialFormValues = {
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
};

const Index = ({ integrationTypes, subscriptions }: Props) => {
  const { data, setData, errors, post, processing } =
    useForm(initialFormValues);

  function handleSubmit(e: FormEvent<HTMLFormElement>) {
    e.preventDefault();
    post('/integrations');
  }

  return (
    <div className="flex flex-col gap-5">
      <Heading level={2}>Integratie toevoegen</Heading>

      <form onSubmit={handleSubmit} className="flex flex-col gap-5">
        <FormElement
          label="Type integratie"
          labelSize="xl"
          component={
            <select
              name="integrationType"
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
          }
          error={errors.integrationType}
        />
        <FormElement
          label="Plan"
          labelSize="xl"
          component={
            <select
              name="subscriptionId"
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
          }
          error={errors.subscriptionId}
        />
        <FormElement
          label="Naam integratie"
          labelSize="xl"
          component={
            <input
              type="text"
              name="name"
              value={data.name}
              onChange={(e) => setData('name', e.target.value)}
            />
          }
          error={errors.name}
        />
        <FormElement
          label="Doel van de integratie"
          labelSize="xl"
          component={
            <input
              type="text"
              name="description"
              value={data.description}
              onChange={(e) => setData('description', e.target.value)}
            />
          }
          error={errors.description}
        />

        <div>
          <span className="text-xl">Contact organisatie</span>
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <FormElement
              label="Voornaam"
              component={
                <input
                  type="text"
                  name="firstNameOrganisation"
                  value={data.firstNameOrganisation}
                  onChange={(e) =>
                    setData('firstNameOrganisation', e.target.value)
                  }
                  placeholder="Voornaam"
                />
              }
              error={errors.firstNameOrganisation}
            />
            <FormElement
              label="Achternaam"
              component={
                <input
                  type="text"
                  name="lastNameOrganisation"
                  value={data.lastNameOrganisation}
                  onChange={(e) =>
                    setData('lastNameOrganisation', e.target.value)
                  }
                  placeholder="Achternaam"
                />
              }
              error={errors.lastNameOrganisation}
            />
            <FormElement
              label="Email"
              component={
                <input
                  type="email"
                  name="emailOrganisation"
                  value={data.emailOrganisation}
                  onChange={(e) => setData('emailOrganisation', e.target.value)}
                  placeholder="Email"
                />
              }
              error={errors.emailOrganisation}
            />
          </div>
        </div>

        <div>
          <span className="text-xl">Contact technische partner</span>
          <div className="grid grid-cols-1 lg:grid-cols-3 gap-3">
            <FormElement
              label="Firstname"
              component={
                <input
                  type="text"
                  name="firstNamePartner"
                  value={data.firstNamePartner}
                  onChange={(e) => setData('firstNamePartner', e.target.value)}
                  placeholder="Voornaam"
                />
              }
              error={errors.firstNamePartner}
            />
            <FormElement
              label="Lastname"
              component={
                <input
                  type="text"
                  name="lastNamePartner"
                  value={data.lastNamePartner}
                  onChange={(e) => setData('lastNamePartner', e.target.value)}
                  placeholder="Achternaam"
                />
              }
              error={errors.lastNamePartner}
            />
            <FormElement
              label="Email"
              component={
                <input
                  type="email"
                  name="emailPartner"
                  value={data.emailPartner}
                  onChange={(e) => setData('emailPartner', e.target.value)}
                  placeholder="Email"
                />
              }
              error={errors.emailPartner}
            />
          </div>
        </div>

        <div>
          <span className="text-xl">Gebruiksvoorwaarden</span>
          <FormElement
            label="Ik ga akkoord"
            labelPosition="right"
            component={<input type="checkbox" name="agreement" />}
            error={errors.emailPartner}
          />
        </div>

        <button
          type="submit"
          disabled={processing}
          className="px-4 py-2 font-semibold text-sm bg-cyan-500 text-white rounded-full shadow-sm"
        >
          Integratie aanmaken
        </button>
      </form>
    </div>
  );
};

Index.layout = (page: ReactNode) => <Layout>{page}</Layout>;

export default Index;
