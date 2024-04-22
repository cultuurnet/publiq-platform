import React, { useState } from "react";
import { PubliqLogo } from "./logos/PubliqLogo";
import { VlaanderenLogo } from "./logos/VlaanderenLogo";
import { Heading } from "./Heading";
import { ButtonLink } from "./ButtonLink";
import { useTranslation } from "react-i18next";
import { FontAwesomeIcon } from "@fortawesome/react-fontawesome";
import { faChevronRight } from "@fortawesome/free-solid-svg-icons";
import { NewsletterDialog } from "./NewsletterDialog";
import { Link } from "./Link";

export default function Footer() {
  const { t } = useTranslation();
  const [isNewsletterDialogVisible, setIsNewsletterDialogVisible] =
    useState(false);

  return (
    <footer
      className="bg-publiq-blue text-white w-full flex justify-between px-7 py-7 max-md:flex-col gap-7"
      data-component-name="footer"
    >
      <div className="flex flex-col gap-6">
        <PubliqLogo color="white" width={140} height={114} />
        <div className="flex flex-col gap-7">
          <div className="flex flex-col gap-1">
            <Heading level={3} className="font-medium">
              {t("global.publiq")}
            </Heading>
            <address className="font-light not-italic tracking-wide">
              {t("footer.address.part1")}
              <br />
              {t("footer.address.part2")}
            </address>
          </div>
          <div className="flex flex-col gap-1">
            <div className="flex gap-3">
              <span>{t("global.btw")}</span>
              <span className="font-extralight">BE 0475 250 609</span>
            </div>
            <div className="flex gap-3">
              <span>{t("global.iban")}</span>
              <span className="font-extralight">BE87 7330 0837 7594</span>
            </div>
          </div>
        </div>
      </div>
      <div className="flex flex-col gap-5 text-lg font-light">
        <div className="flex flex-col gap-1">
          <Heading level={3} className="font-medium">
            {t("title")}
          </Heading>
          <Link
            href={t("footer.links.opportunities.link")}
            className="hover:underline"
          >
            {t("footer.links.opportunities.label")}
          </Link>
          <Link href={t("footer.links.price.link")} className="hover:underline">
            {t("footer.links.price.label")}
          </Link>
        </div>
        <div className="flex flex-col gap-1">
          <Heading level={3} className="font-medium">
            {t("footer.legal")}
          </Heading>
          <Link
            href={t("footer.links.terms_of_use.link")}
            className="hover:underline"
          >
            {t("footer.links.terms_of_use.label")}
          </Link>
          <Link
            href={t("footer.links.privacy.link")}
            className="hover:underline"
          >
            {t("footer.links.privacy.label")}
          </Link>
          <Link
            href={t("footer.links.cookie.link")}
            className="hover:underline"
          >
            {t("footer.links.cookie.label")}
          </Link>
          <Link
            href={t("footer.links.responsible_disclosure_policy.link")}
            className="hover:underline"
          >
            {t("footer.links.responsible_disclosure_policy.label")}
          </Link>
        </div>
      </div>
      <div className="flex flex-col font-light text-lg">
        <div className="flex flex-col gap-1">
          <Heading level={3} className="font-medium">
            {t("footer.support")}
          </Heading>
          <Link
            href={t("footer.links.documentation.link")}
            className="hover:underline"
          >
            {t("footer.links.documentation.label")}
          </Link>
          <Link
            href={t("footer.links.status_page.link")}
            className="hover:underline"
          >
            {t("footer.links.status_page.label")}
          </Link>
          <Link href={t("footer.links.help.link")} className="hover:underline">
            {t("footer.links.help.label")}
          </Link>
          <Link href="#" className="hover:underline">
            {t("footer.links.slack")}
          </Link>
        </div>
      </div>
      <div className="flex flex-col gap-3 max-md:gap-7 ">
        <div className="max-w-[23rem] shadow-lg h-auto bg-white relative md:top-[-5rem] flex flex-col gap-5 p-5 font-medium">
          <Heading level={3} className="text-publiq-gray-900 font-bold">
            {t("footer.newsletter.title")}
          </Heading>
          <p className="text-publiq-gray-900 text-lg font-light">
            {t("footer.newsletter.description")}
          </p>
          <ButtonLink
            href="#"
            className="self-start"
            contentStyles="flex gap-2 items-center"
            onClick={() => setIsNewsletterDialogVisible(true)}
          >
            <span>{t("footer.newsletter.action")}</span>
            <FontAwesomeIcon size="xs" icon={faChevronRight} />
          </ButtonLink>
          <NewsletterDialog
            isVisible={isNewsletterDialogVisible}
            onClose={() => setIsNewsletterDialogVisible(false)}
          />
        </div>
        <div className="md:self-end">
          <VlaanderenLogo />
        </div>
      </div>
    </footer>
  );
}
