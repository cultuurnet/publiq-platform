export type Organization = {
  id: string;
  name: string;
  invoiceMail: string;
  vat: string;
  address: {
    street: string;
    zip: string;
    city: string;
    country: string;
  };
};
