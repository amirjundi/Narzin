import React from "react";
import { useTranslation } from "react-i18next";

const Return = () => {
  const { t, i18n } = useTranslation();
  const isRTL = i18n.language === 'ar';

  return (
    <div className={`min-h-screen bg-base-100 ${isRTL ? 'rtl' : 'ltr'}`}>
      <div className="container mx-auto px-4 py-8 max-w-4xl">
        <div className="prose max-w-none">
          <h1 className="text-4xl font-bold mb-8 text-center">
            {t("return.title")}
          </h1>
          
          <div className="text-sm text-gray-600 mb-6">
            {t("return.lastUpdated")}: {new Date().toLocaleDateString()}
          </div>

          {/* Introduction */}
          <section className="mb-8">
            <div className="alert alert-info mb-6">
              <div>
                <h3 className="font-bold">{t("return.summary.title")}</h3>
                <ul className="mt-2 space-y-1">
                  <li>• {t("return.summary.period")}</li>
                  <li>• {t("return.summary.condition")}</li>
                  <li>• {t("return.summary.shipping")}</li>
                  <li>• {t("return.summary.refund")}</li>
                </ul>
              </div>
            </div>
          </section>

          {/* Return Period */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("return.period.title")}</h2>
            <div className="bg-base-200 p-4 rounded-lg mb-4">
              <p className="font-medium text-lg mb-2">{t("return.period.main")}</p>
              <p className="text-sm">{t("return.period.calculation")}</p>
            </div>
            
            <h3 className="text-xl font-medium mb-3">{t("return.period.exceptions.title")}</h3>
            <ul className="list-disc pl-6 space-y-2">
              <li>{t("return.period.exceptions.electronics")}</li>
              <li>{t("return.period.exceptions.clothing")}</li>
              <li>{t("return.period.exceptions.books")}</li>
            </ul>
          </section>

          {/* Eligible Items */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("return.eligible.title")}</h2>
            <div className="grid md:grid-cols-2 gap-6">
              <div className="bg-green-50 p-4 rounded-lg">
                <h3 className="text-lg font-medium text-green-800 mb-3">
                  ✓ {t("return.eligible.returnable.title")}
                </h3>
                <ul className="space-y-2 text-green-700">
                  <li>• {t("return.eligible.returnable.unused")}</li>
                  <li>• {t("return.eligible.returnable.packaging")}</li>
                  <li>• {t("return.eligible.returnable.tags")}</li>
                  <li>• {t("return.eligible.returnable.accessories")}</li>
                </ul>
              </div>
              
              <div className="bg-red-50 p-4 rounded-lg">
                <h3 className="text-lg font-medium text-red-800 mb-3">
                  ✗ {t("return.eligible.nonReturnable.title")}
                </h3>
                <ul className="space-y-2 text-red-700">
                  <li>• {t("return.eligible.nonReturnable.hygiene")}</li>
                  <li>• {t("return.eligible.nonReturnable.food")}</li>
                  <li>• {t("return.eligible.nonReturnable.customized")}</li>
                  <li>• {t("return.eligible.nonReturnable.digital")}</li>
                  <li>• {t("return.eligible.nonReturnable.intimate")}</li>
                </ul>
              </div>
            </div>
          </section>

          {/* Return Process */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("return.process.title")}</h2>
            <div className="steps steps-vertical lg:steps-horizontal w-full">
              <div className="step step-primary">
                <div className="step-content">
                  <h3 className="font-medium">{t("return.process.step1.title")}</h3>
                  <p className="text-sm">{t("return.process.step1.desc")}</p>
                </div>
              </div>
              <div className="step step-primary">
                <div className="step-content">
                  <h3 className="font-medium">{t("return.process.step2.title")}</h3>
                  <p className="text-sm">{t("return.process.step2.desc")}</p>
                </div>
              </div>
              <div className="step step-primary">
                <div className="step-content">
                  <h3 className="font-medium">{t("return.process.step3.title")}</h3>
                  <p className="text-sm">{t("return.process.step3.desc")}</p>
                </div>
              </div>
              <div className="step step-primary">
                <div className="step-content">
                  <h3 className="font-medium">{t("return.process.step4.title")}</h3>
                  <p className="text-sm">{t("return.process.step4.desc")}</p>
                </div>
              </div>
            </div>
          </section>

          {/* Shipping & Costs */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("return.shipping.title")}</h2>
            <div className="overflow-x-auto">
              <table className="table table-zebra w-full">
                <thead>
                  <tr>
                    <th>{t("return.shipping.table.reason")}</th>
                    <th>{t("return.shipping.table.cost")}</th>
                    <th>{t("return.shipping.table.refund")}</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td>{t("return.shipping.table.defective")}</td>
                    <td className="text-green-600 font-medium">{t("return.shipping.table.free")}</td>
                    <td>{t("return.shipping.table.fullRefund")}</td>
                  </tr>
                  <tr>
                    <td>{t("return.shipping.table.wrong")}</td>
                    <td className="text-green-600 font-medium">{t("return.shipping.table.free")}</td>
                    <td>{t("return.shipping.table.fullRefund")}</td>
                  </tr>
                  <tr>
                    <td>{t("return.shipping.table.changeOfMind")}</td>
                    <td className="text-orange-600 font-medium">{t("return.shipping.table.customerPays")}</td>
                    <td>{t("return.shipping.table.minusShipping")}</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </section>

          {/* Refund Process */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("return.refund.title")}</h2>
            <div className="bg-blue-50 p-4 rounded-lg mb-4">
              <p className="font-medium mb-2">{t("return.refund.timeline")}</p>
              <p className="text-sm">{t("return.refund.method")}</p>
            </div>
            
            <h3 className="text-xl font-medium mb-3">{t("return.refund.processing.title")}</h3>
            <ul className="list-disc pl-6 space-y-2">
              <li>{t("return.refund.processing.inspection")}</li>
              <li>{t("return.refund.processing.approval")}</li>
              <li>{t("return.refund.processing.bank")}</li>
              <li>{t("return.refund.processing.notification")}</li>
            </ul>
          </section>

          {/* Exchanges */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("return.exchange.title")}</h2>
            <p className="mb-4">{t("return.exchange.content")}</p>
            <div className="alert alert-warning">
              <div>
                <h3 className="font-bold">{t("return.exchange.note.title")}</h3>
                <p>{t("return.exchange.note.content")}</p>
              </div>
            </div>
          </section>

          {/* International Returns */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("return.international.title")}</h2>
            <div className="bg-yellow-50 p-4 rounded-lg">
              <ul className="space-y-2">
                <li>• {t("return.international.period")}</li>
                <li>• {t("return.international.shipping")}</li>
                <li>• {t("return.international.duties")}</li>
                <li>• {t("return.international.processing")}</li>
              </ul>
            </div>
          </section>

          {/* Damaged Items */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("return.damaged.title")}</h2>
            <p className="mb-4">{t("return.damaged.content")}</p>
            <div className="bg-red-50 p-4 rounded-lg">
              <h3 className="font-bold text-red-800 mb-2">{t("return.damaged.requirements.title")}</h3>
              <ul className="text-red-700 space-y-1">
                <li>• {t("return.damaged.requirements.photos")}</li>
                <li>• {t("return.damaged.requirements.packaging")}</li>
                <li>• {t("return.damaged.requirements.report")}</li>
                <li>• {t("return.damaged.requirements.contact")}</li>
              </ul>
            </div>
          </section>

          {/* Contact Information */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("return.contact.title")}</h2>
            <div className="grid md:grid-cols-2 gap-4">
              <div className="bg-base-200 p-4 rounded-lg">
                <h3 className="font-medium mb-2">{t("return.contact.support.title")}</h3>
                <p><strong>{t("return.contact.support.email")}:</strong> returns@narzin-commerce.com</p>
                <p><strong>{t("return.contact.support.phone")}:</strong> +964 XXX XXX XXXX</p>
                <p><strong>{t("return.contact.support.hours")}:</strong> {t("return.contact.support.hoursValue")}</p>
              </div>
              
              <div className="bg-base-200 p-4 rounded-lg">
                <h3 className="font-medium mb-2">{t("return.contact.address.title")}</h3>
                <p>Narzin-Commerce Returns</p>
                <p>{t("return.contact.address.value")}</p>
                <p>{t("return.contact.address.city")}</p>
              </div>
            </div>
          </section>

          {/* Legal Compliance */}
          <section className="mb-8">
            <h2 className="text-2xl font-semibold mb-4">{t("return.legal.title")}</h2>
            <div className="bg-base-200 p-4 rounded-lg">
              <p className="mb-2">{t("return.legal.eu")}</p>
              <p className="mb-2">{t("return.legal.iraq")}</p>
              <p>{t("return.legal.rights")}</p>
            </div>
          </section>
        </div>
      </div>
    </div>
  );
};

export default Return;