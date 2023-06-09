import React from "react";
import type { Integration } from "../Pages/Integrations/Index";
import { IconButton } from "./IconButton";
import { faCopy, faPencil, faTrash } from "@fortawesome/free-solid-svg-icons";
import { Heading } from "./Heading";
import { Card } from "./Card";
import { useTranslation } from "react-i18next";
import { Link } from "./Link";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";

type Props = Integration & {
  onDelete: (id: string) => void;
};

export const IntegrationCard = ({
  id,
  name,
  type,
  description,
  status,
  onDelete,
}: Props) => {
  const { t } = useTranslation();

  const handleCopyToClipboard = () => {
    console.log(`copy ${id} to clipboard`);
  };

  return (
    <Card
      title={
        <div className="inline-flex w-full justify-between">
          <div className="inline-flex gap-3 items-center">
            <Heading level={2}>{name}</Heading>
            <span>{type}</span>
          </div>
          <div className="inline-flex gap-3">
            <IconButton icon={faPencil} />
            <IconButton
              icon={faTrash}
              className="text-red-500"
              onClick={() => onDelete(id)}
            />
          </div>
        </div>
      }
      description={description}
      className="w-full"
    >
      <div className="flex flex-col gap-2">
        <section className="inline-flex gap-3 items-center">
          <Heading level={3}>{t("integrations.test")}</Heading>
          <div className="inline-flex gap-2 items-center bg-gray-200 py-2 px-3">
            <span>{id}</span>
            <button onClick={handleCopyToClipboard}>
              <FontAwesomeIcon
                icon={faCopy}
                className="text-gray-600 h-[1.2rem]"
              />
            </button>
          </div>
        </section>
        <section className="inline-flex gap-3 items-center">
          <Heading level={3}>{t("integrations.live")}</Heading>
          <span>{t(`integrations.status.${status}`)}</span>
        </section>
        <section className="inline-flex gap-3 items-center">
          <Heading level={3}>{t("integrations.documentation.title")}</Heading>
          <Link href="#">
            {t("integrations.documentation.action_title", {
              product: t(`integrations.products.${type}`),
            })}
          </Link>
        </section>
      </div>
    </Card>
  );
};
