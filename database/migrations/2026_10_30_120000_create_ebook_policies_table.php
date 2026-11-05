<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ebook_policies', function (Blueprint $table) {
            $table->id();
            $table->string('slug', 32)->unique();
            $table->string('title', 180);
            $table->longText('content');
            $table->boolean('is_published')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        $publishedAt = now();
        $policies = [
            [
                'slug' => 'refund',
                'title' => 'eBook Refund Policy',
                'content' => <<<'MARKDOWN'
**Draft policy. Please obtain Nigerian legal review before relying on or changing this policy.** This draft does not limit any rights that cannot lawfully be excluded.

## 1. Who we are

These terms apply to digital eBooks sold by MephEd Educational Services Ltd ("MephEd", "we", "us"). For help with an order, email [info@mephed.ng](mailto:info@mephed.ng) and include the order email and payment reference. Do not send full card details.

## 2. Digital products and access

Our eBooks are digital products. After a payment is confirmed, we send download instructions to the email address provided at checkout. Access instructions may expire or have a download limit; any limit that applies will be shown with the product or delivery instructions. Please contact us promptly if a link is missing, expired, or not working.

## 3. When to contact us about a refund

Contact us if you were charged more than once for the same order, if a payment succeeded but the purchased file was not delivered, or if the file is materially damaged or cannot be accessed. We will verify the transaction and investigate the delivery or access problem. Where appropriate, we will restore access or arrange a refund through the payment method or process available to us.

If you believe a purchase was made without your authorization, contact your payment provider promptly and let us know the payment reference so we can investigate.

## 4. Change of mind

Because an eBook is a digital product delivered electronically, we do not generally offer refunds solely because a buyer changes their mind or no longer wants the product after delivery. This does not exclude any refund, cancellation, or other consumer right that applies under Nigerian law.

## 5. How to request help

Email [info@mephed.ng](mailto:info@mephed.ng) with the order email, payment reference, product title, and a description of the problem. Never email your password, card number, PIN, or one-time passcode. We may ask for reasonable information needed to identify the transaction; do not send more personal or payment information than necessary.

## 6. Applicable rights

Nothing in this policy removes rights or remedies that applicable consumer-protection or other laws do not allow us to limit. If this policy conflicts with a mandatory legal right, that legal right applies.

**Last reviewed:** 30 October 2026.
MARKDOWN,
            ],
            [
                'slug' => 'terms',
                'title' => 'eBook Terms of Sale',
                'content' => <<<'MARKDOWN'
**Draft terms. Please obtain Nigerian legal review before relying on or changing these terms.** These terms do not limit any rights that cannot lawfully be excluded.

## 1. Seller and contact

The seller is MephEd Educational Services Ltd ("MephEd", "we", "us"). Contact us at [info@mephed.ng](mailto:info@mephed.ng). These terms apply to purchases of digital eBooks offered through the MephEd website.

## 2. Products, prices, and orders

Product descriptions, included materials, and prices are shown on the relevant product page. Prices are displayed in Nigerian naira unless stated otherwise. Your order is submitted when you provide the requested checkout details and proceed to payment. A payment is accepted only after it is confirmed by our payment provider and our records.

Payments are processed by Paystack or another provider identified at checkout. The provider may apply its own terms and privacy notice to payment processing. Do not send card details, PINs, or one-time passcodes to MephEd.

## 3. Digital delivery and permitted use

After payment confirmation, we send access or download instructions to the email address provided with the order. The buyer is responsible for providing an accurate email address and keeping the delivery link private. Delivery links may have an expiry period or download limit; any applicable limits are shown with the product or delivery instructions.

Unless the product page or a separate written licence says otherwise, a purchase is for the buyer's personal, non-commercial use. You may not resell, republish, publicly distribute, or share the eBook or its download link. Copyright and other intellectual-property rights remain with their respective owners.

## 4. Access problems and refunds

If payment is confirmed but delivery fails, a file is damaged, or an access link does not work, contact [info@mephed.ng](mailto:info@mephed.ng) with your order email and payment reference. We will investigate and, where appropriate, restore access or arrange a refund. See our [eBook Refund Policy](/legal/refund). Nothing in these terms removes a right that applicable law does not allow us to exclude.

## 5. Information and educational content

Our eBooks provide general educational information. They are not a promise of earnings, employment, business results, or a particular outcome, and are not a substitute for professional advice suited to your circumstances. You are responsible for evaluating and applying information appropriately.

## 6. Availability and changes

We may correct errors, update product information, or temporarily suspend access for maintenance, security, or operational reasons. We will not use this section to remove rights relating to an order already placed that cannot lawfully be removed.

## 7. Responsibility and legal rights

Nothing in these terms excludes or limits liability, consumer rights, or remedies where applicable law does not permit that exclusion or limitation. Subject to that rule, we are not responsible for loss caused by incorrect contact details supplied at checkout, disclosure of a buyer's private download link, or use of a product outside the licence above.

## 8. Privacy and complaints

Our [eBook Privacy Notice](/legal/privacy) explains how order information is handled. For questions or complaints about an order, contact [info@mephed.ng](mailto:info@mephed.ng) and include the payment reference.

## 9. Governing law

These terms are intended to be governed by the laws of the Federal Republic of Nigeria, subject to any mandatory consumer protections and jurisdictional rules that apply to you.

**Last reviewed:** 30 October 2026.
MARKDOWN,
            ],
            [
                'slug' => 'privacy',
                'title' => 'eBook Privacy Notice',
                'content' => <<<'MARKDOWN'
**Draft privacy notice. Please obtain Nigerian data-protection review before relying on or changing this notice.** This notice describes the intended handling of information for MephEd eBook enquiries and purchases.

## 1. Who is responsible

MephEd Educational Services Ltd ("MephEd", "we", "us") is responsible for personal information handled for eBook enquiries, orders, delivery, and support. Contact us at [info@mephed.ng](mailto:info@mephed.ng) with privacy questions or requests.

## 2. Information we handle

Depending on how you use the eBook service, we may handle your name, email address, optional phone number, product and order details, payment references and status, support correspondence, and delivery or download records. Our website may also record basic request, referrer, or campaign information used to operate the service, protect it, and understand how visitors find it. We do not need or ask you to send us your card number, PIN, or one-time passcode.

## 3. Why we use information

We use relevant information to respond to enquiries; process and verify orders; deliver eBooks; provide customer support; detect fraud, abuse, and payment or delivery problems; maintain business and security records; meet legal obligations; and improve the service. We use information for these purposes only where an applicable legal basis permits.

## 4. Service providers and disclosures

We share only information needed for a task with service providers that support payment processing, website hosting, email delivery, storage, security, or site operations. Paystack or the payment provider shown at checkout processes payment information under its own terms and privacy notice. We may also disclose information where required by law, to protect rights and safety, or to handle a dispute or suspected fraud.

## 5. Retention and security

We keep information for as long as reasonably needed for the purposes above, including order administration, accounting, legal obligations, security, and dispute handling. Retention periods depend on the type of record and applicable requirements. We use reasonable safeguards, but no internet transmission or storage system can be guaranteed completely secure.

## 6. Your choices and rights

Subject to applicable law and identity checks, you may ask us to confirm, access, or correct personal information, or ask about deletion, restriction, objection, or consent withdrawal where those rights apply. Some records may need to be retained to complete an order or meet legal obligations. Contact [info@mephed.ng](mailto:info@mephed.ng) to make a request or raise a concern. You may also contact the relevant Nigerian data-protection authority where applicable.

## 7. Children

The eBook store is intended for adults. A parent or guardian who believes a child has supplied personal information may contact us to discuss it.

## 8. Changes to this notice

We may update this notice when our service or legal obligations change. The current version and its review date will appear on this page.

**Last reviewed:** 30 October 2026.
MARKDOWN,
            ],
        ];

        foreach ($policies as &$policy) {
            $policy['is_published'] = true;
            $policy['published_at'] = $publishedAt;
            $policy['created_at'] = $publishedAt;
            $policy['updated_at'] = $publishedAt;
        }

        DB::table('ebook_policies')->insert($policies);
    }

    public function down(): void
    {
        Schema::dropIfExists('ebook_policies');
    }
};
