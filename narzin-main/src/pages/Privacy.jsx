import React from "react";
import { useTranslation } from "react-i18next";

const Privacy = () => {
  const { t, i18n } = useTranslation();
  const isRTL = i18n.language === 'ar';

  return (
    <div className={`min-h-screen bg-base-100 ${isRTL ? 'rtl' : 'ltr'}`}>
      <div className="container mx-auto px-4 py-8 max-w-4xl">
        <div className="prose max-w-none">
          <h1 className="text-4xl font-bold mb-8 text-center">
            {t("privacy.title")}
          </h1>
          
          <div className="text-sm text-gray-600 mb-6">
            {t("privacy.lastUpdated")}: {new Date().toLocaleDateString()}
          </div>

          {/* Introduction */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("privacy.introduction.title")}</h2>
            <p className="mb-4">{t("privacy.introduction.content")}</p>
          </section>

          {/* Data Controller */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("privacy.controller.title")}</h2>
            <div className="bg-base-200 p-4 rounded-lg">
              <p><strong>{t("privacy.controller.company")}</strong> Narzin-Commerce</p>
              <p><strong>{t("privacy.controller.address")}</strong> {t("privacy.controller.addressValue")}</p>
              <p><strong>{t("privacy.controller.email")}</strong> privacy@narzin-commerce.com</p>
              <p><strong>{t("privacy.controller.phone")}</strong> +964 XXX XXX XXXX</p>
            </div>
          </section>

          {/* Data Collection */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("privacy.collection.title")}</h2>
            <h3 className="text-xl font-medium mb-3">{t("privacy.collection.personal.title")}</h3>
            <ul className="list-disc pl-6 mb-4 space-y-2">
              <li>{t("privacy.collection.personal.name")}</li>
              <li>{t("privacy.collection.personal.email")}</li>
              <li>{t("privacy.collection.personal.phone")}</li>
              <li>{t("privacy.collection.personal.address")}</li>
              <li>{t("privacy.collection.personal.payment")}</li>
            </ul>
            
            <h3 className="text-xl font-medium mb-3">{t("privacy.collection.technical.title")}</h3>
            <ul className="list-disc pl-6 mb-4 space-y-2">
              <li>{t("privacy.collection.technical.ip")}</li>
              <li>{t("privacy.collection.technical.browser")}</li>
              <li>{t("privacy.collection.technical.cookies")}</li>
              <li>{t("privacy.collection.technical.usage")}</li>
            </ul>
          </section>

          {/* Legal Basis */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("privacy.legal.title")}</h2>
            <ul className="list-disc pl-6 space-y-2">
              <li>{t("privacy.legal.contract")}</li>
              <li>{t("privacy.legal.consent")}</li>
              <li>{t("privacy.legal.legitimate")}</li>
              <li>{t("privacy.legal.compliance")}</li>
            </ul>
          </section>

          {/* Data Usage */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("privacy.usage.title")}</h2>
            <ul className="list-disc pl-6 space-y-2">
              <li>{t("privacy.usage.orders")}</li>
              <li>{t("privacy.usage.communication")}</li>
              <li>{t("privacy.usage.support")}</li>
              <li>{t("privacy.usage.improvement")}</li>
              <li>{t("privacy.usage.legal")}</li>
            </ul>
          </section>

          {/* Data Sharing */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("privacy.sharing.title")}</h2>
            <p className="mb-4">{t("privacy.sharing.intro")}</p>
            <ul className="list-disc pl-6 space-y-2">
              <li>{t("privacy.sharing.payment")}</li>
              <li>{t("privacy.sharing.shipping")}</li>
              <li>{t("privacy.sharing.analytics")}</li>
              <li>{t("privacy.sharing.legal")}</li>
            </ul>
          </section>

          {/* Your Rights */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("privacy.rights.title")}</h2>
            <div className="bg-blue-50 p-4 rounded-lg">
              <ul className="list-disc pl-6 space-y-2">
                <li><strong>{t("privacy.rights.access.title")}:</strong> {t("privacy.rights.access.desc")}</li>
                <li><strong>{t("privacy.rights.rectification.title")}:</strong> {t("privacy.rights.rectification.desc")}</li>
                <li><strong>{t("privacy.rights.erasure.title")}:</strong> {t("privacy.rights.erasure.desc")}</li>
                <li><strong>{t("privacy.rights.portability.title")}:</strong> {t("privacy.rights.portability.desc")}</li>
                <li><strong>{t("privacy.rights.restriction.title")}:</strong> {t("privacy.rights.restriction.desc")}</li>
                <li><strong>{t("privacy.rights.objection.title")}:</strong> {t("privacy.rights.objection.desc")}</li>
              </ul>
            </div>
          </section>

          {/* Data Security */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("privacy.security.title")}</h2>
            <p className="mb-4">{t("privacy.security.content")}</p>
            <ul className="list-disc pl-6 space-y-2">
              <li>{t("privacy.security.encryption")}</li>
              <li>{t("privacy.security.access")}</li>
              <li>{t("privacy.security.monitoring")}</li>
              <li>{t("privacy.security.updates")}</li>
            </ul>
          </section>

          {/* Data Retention */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("privacy.retention.title")}</h2>
            <ul className="list-disc pl-6 space-y-2">
              <li>{t("privacy.retention.account")}</li>
              <li>{t("privacy.retention.orders")}</li>
              <li>{t("privacy.retention.analytics")}</li>
              <li>{t("privacy.retention.marketing")}</li>
            </ul>
          </section>

          {/* Cookies */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("privacy.cookies.title")}</h2>
            <p className="mb-4">{t("privacy.cookies.content")}</p>
            <ul className="list-disc pl-6 space-y-2">
              <li><strong>{t("privacy.cookies.essential.title")}:</strong> {t("privacy.cookies.essential.desc")}</li>
              <li><strong>{t("privacy.cookies.analytics.title")}:</strong> {t("privacy.cookies.analytics.desc")}</li>
              <li><strong>{t("privacy.cookies.marketing.title")}:</strong> {t("privacy.cookies.marketing.desc")}</li>
            </ul>
          </section>

          {/* International Transfers */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("privacy.transfers.title")}</h2>
            <p className="mb-4">{t("privacy.transfers.content")}</p>
          </section>

          {/* Contact */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("privacy.contact.title")}</h2>
            <div className="bg-base-200 p-4 rounded-lg">
              <p className="mb-2">{t("privacy.contact.intro")}</p>
              <p><strong>{t("privacy.contact.email")}:</strong> privacy@narzin-commerce.com</p>
              <p><strong>{t("privacy.contact.phone")}:</strong> +964 XXX XXX XXXX</p>
              <p><strong>{t("privacy.contact.address")}:</strong> {t("privacy.controller.addressValue")}</p>
            </div>
          </section>

          {/* Changes */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("privacy.changes.title")}</h2>
            <p>{t("privacy.changes.content")}</p>
          </section>
        </div>
      </div>
    </div>
  );
};

export default Privacy;