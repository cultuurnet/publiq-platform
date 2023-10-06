import React, { ComponentProps } from "react";
import { ContactFormData } from "./Integrations/Detail/ContactInfo";
import { ContactsTableDesktop } from "./ContactsTableDesktop";
import { ContactsTableMobile } from "./ContactsTableMobile";
import { ContactsTableContent } from "./ContactsTableContent";

export type ContactsTableProps = {
  data: ContactFormData;
  onEdit: (id: string) => void;
  onDelete: (id: string) => void;
  onPreview: (bool: boolean) => void;
  functionalId: string;
  technicalId: string;
} & ComponentProps<"div">;

export const ContactsTable = (props: ContactsTableProps) => {
  return (
    <>
      <ContactsTableDesktop className="max-md:hidden">
        <ContactsTableContent {...props} desktop />
      </ContactsTableDesktop>
      <ContactsTableMobile className="md:hidden">
        <ContactsTableContent {...props} mobile />
      </ContactsTableMobile>
    </>
  );
};
