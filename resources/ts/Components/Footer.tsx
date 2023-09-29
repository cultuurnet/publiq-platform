import React from "react";
import { PubliqLogo } from "./logos/PubliqLogo";
import { VlaanderenLogo } from "./logos/VlaanderenLogo";
import { Heading } from "./Heading";
import { ButtonLink } from "./ButtonLink";
import { Link } from "@inertiajs/react";
import { useTranslation } from "react-i18next";

export default function Footer() {
  const { t } = useTranslation();
  return (
    <footer
      className="bg-publiq-blue text-white w-full flex justify-between px-7 py-10 max-md:flex-col gap-7"
      data-component-name="footer"
    >
      <div className="flex-col">
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
      <div className="flex flex-col gap-7 text-lg font-light">
        <Heading level={3} className="font-medium">
          {t("title")}
        </Heading>
        <div className="flex flex-col gap-3">
          <Link href="#" className="hover:underline">
            {t("footer.links.what")}
          </Link>
          <Link href="#" className="hover:underline">
            {t("footer.links.opportunities")}
          </Link>
          <Link href="#" className="hover:underline">
            {t("footer.links.price")}
          </Link>
          <Link href="#" className="hover:underline">
            {t("footer.links.start")}
          </Link>
        </div>
        <Heading level={3} className="font-medium">
          {t("footer.legal")}
        </Heading>
        <div className="flex flex-col gap-3">
          <Link href="#" className="hover:underline">
            {t("footer.links.terms_of_use")}
          </Link>
          <Link href="#" className="hover:underline">
            {t("footer.links.privacy")}
          </Link>
          <Link href="#" className="hover:underline">
            {t("footer.links.cookie")}
          </Link>
        </div>
      </div>
      <div className="flex flex-col gap-7 font-light text-lg">
        <Heading level={3} className="font-medium">
          {t("footer.support")}
        </Heading>
        <div className="flex flex-col gap-3">
          <Link href="#" className="hover:underline">
            {t("footer.links.documentation")}
          </Link>
          <Link href="#" className="hover:underline">
            {t("footer.links.status_page")}
          </Link>
          <Link href="#" className="hover:underline">
            {t("footer.links.help")}
          </Link>
          <Link href="#" className="hover:underline">
            {t("footer.links.slack")}
          </Link>
        </div>
      </div>
      <div className="flex flex-col gap-5">
        <div className="max-w-[23rem] shadow-lg h-auto bg-white relative md:top-[-7rem] flex flex-col gap-5 p-10 font-medium">
          <Heading level={2} className="text-publiq-gray-dark font-light">
            {t("footer.newsletter.title")}
          </Heading>
          <p className="text-publiq-gray-dark text-lg font-light">
            {t("footer.newsletter.description")}
          </p>
          <ButtonLink
            href="#"
            className="self-start"
            contentStyles="flex gap-2 items-center"
          >
            <span>{t("footer.newsletter.action")}</span>
            <FontAwesomeIcon size="xs" icon={faChevronRight} />
          </ButtonLink>
        </div>
        <div className="lg:self-end">
          <VlaanderenLogo />
        </div>
      </div>
    </footer>
  );
}
