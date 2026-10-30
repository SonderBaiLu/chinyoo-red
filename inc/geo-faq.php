<?php
/**
 * GEO FAQ — 产品页 FAQPage 结构化数据 + 可见问答区块(5 语言)。
 *
 * 目的：让 AI 搜索(ChatGPT / Perplexity / 谷歌 AI 概览)能从产品页直接抽取
 * 选型/使用/认证类问答。每个产品按其主分类匹配一组问答，未命中走 general。
 *
 * 数据：EN 源在此文件；非 EN 翻译在 inc/geo-faq-strings-{zh,es,ru,ar}.php
 * (id => 译文 的 dict，由翻译 workflow 生成)。
 *
 * @package chinyoo-red
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** 9 个主分类(EN slug => 显示名)。产品解析 FAQ 时按此优先。 */
function ew_geo_faq_categories() {
	return array(
		'basic-voltage-testers'            => 'Basic Voltage Testers',
		'basic-digital-multimeters'        => 'Basic Digital Multimeters',
		'digital-voltage-testers'          => 'Digital Voltage Testers',
		'digital-clamp-meters'             => 'Digital Clamp Meters',
		'car-electric-pens'                => 'Car Electric Pens',
		'professional-digital-multimeters' => 'Professional Digital Multimeters',
		'inductive-alarm-testers'          => 'Inductive Alarm Testers',
		'battery-meters'                   => 'Battery Meters',
		'socket-testers'                   => 'Socket Testers',
	);
}

/**
 * EN 源问答。id 为翻译 key 前缀：问题 {id}q、答案 {id}a。
 */
function ew_geo_faq_all() {
	return array(
		'general' => array(
			array( 'id' => 'g1', 'q' => 'What is the minimum order quantity for wholesale?', 'a' => 'We serve both small trial orders and container-scale wholesale. Tell us the models and quantity you need, and our sales team will reply with pricing and lead time within 24 hours.' ),
			array( 'id' => 'g2', 'q' => 'Do you support OEM/ODM with our own brand?', 'a' => 'Yes. As a factory founded in 2008, we offer OEM/ODM services including custom labels, packaging and adjustable measuring ranges. Send us your specifications and we will handle the rest.' ),
			array( 'id' => 'g3', 'q' => 'What certifications do your instruments have?', 'a' => 'Our instruments are CE, RoHS and ISO 9001 compliant, and we can provide the relevant certificates on request.' ),
			array( 'id' => 'g4', 'q' => 'What is your warranty?', 'a' => 'Every instrument ships with a 12-month warranty. If a unit is defective, contact us and we will arrange a replacement or repair.' ),
			array( 'id' => 'g5', 'q' => 'How do you ship orders, and what is the lead time?', 'a' => 'Orders ship from our factory in Wenzhou, and we can arrange FOB Wenzhou or FOB Ningbo, whichever suits your freight forwarder best. For sea freight we pack into cartons and pallets sized for a 20ft or 40ft container, and for small or urgent orders we ship by air or express. In-stock catalog models ship within 3 to 7 days after payment, while OEM orders with custom branding take 15 to 30 days depending on the work.' ),
			array( 'id' => 'g6', 'q' => 'What are your payment terms?', 'a' => 'For bulk orders we work on T/T bank transfer with a 30% deposit to confirm production and the 70% balance before shipment. For larger amounts we also accept L/C at sight. For sample orders we take PayPal or full T/T in advance, so your first test order stays simple.' ),
			array( 'id' => 'g7', 'q' => 'Can I get samples to evaluate before ordering in bulk?', 'a' => 'Yes, we encourage a sample order before you commit to bulk, so you can check build quality and calibration yourself. Samples are sent by express and usually arrive within 3 to 7 working days, and we refund the sample cost from your first bulk order of the same model. Just send us the models you want through the contact form or email, and we will quote the sample and ship on payment.' ),
			array( 'id' => 'g8', 'q' => 'What export documents do you provide for customs clearance?', 'a' => 'We provide a commercial invoice, packing list and certificate of origin with every shipment, plus CE and RoHS certificates and test reports when needed. Our export team prepares the documents to match your country\'s customs requirements and can amend them if your forwarder requests changes. The documents are sent by email ahead of the goods, so clearance at your port is not delayed.' ),
		),
		'basic-voltage-testers' => array(
			array( 'id' => 'bvt1', 'q' => 'What is a basic voltage tester used for?', 'a' => 'A voltage tester quickly checks whether a circuit or outlet is live. It is the first tool an electrician picks up before touching any wiring.' ),
			array( 'id' => 'bvt2', 'q' => 'What is the difference between a voltage tester and a digital multimeter?', 'a' => 'A voltage tester gives a fast "live or not" answer and is cheaper and simpler to use. A multimeter measures exact voltage, current and resistance values. Many electricians carry both.' ),
			array( 'id' => 'bvt3', 'q' => 'Do your voltage testers work on both AC and DC?', 'a' => 'Yes, our basic voltage testers cover both AC and DC ranges suitable for household and light industrial work. Check the specifications table on each product page for the exact range.' ),
			array( 'id' => 'bvt4', 'q' => 'Are these testers safe to use?', 'a' => 'Yes. They are CE and RoHS compliant and built with insulated housings. Always verify the tester works on a known-live source before and after use, and follow local electrical safety rules.' ),
			array( 'id' => 'bvt5', 'q' => 'I supply electrical wholesalers in Europe and the US. Which basic voltage tester models should I carry as a starter assortment?', 'a' => 'We are CHINYOO, the factory in Wenzhou, and we can help you put together a starter assortment for your channel. The 1AC-D and AC-1 are our compact non-contact detectors for pocket carry, and the 1805A/B/C/D family gives you four detection-level variants under one SKU series. The M100 and M200 round out the value end of the line if your customers want a lower-priced basic tester. Check the specifications table on the product page to match each model\'s rated voltage range to your local 120V or 230V supply.' ),
			array( 'id' => 'bvt6', 'q' => 'We plan to order the 1805A/B/C/D family. Can we mix the four voltage variants in one order, and how should we split the quantities?', 'a' => 'Yes. The 1805A/B/C/D is one SKU series with four rated-voltage variants, so you can order a mix that matches the voltages your customers work with and manage it as a single part number. If you prefer a simpler pocket pen instead, the 1AC-D and AC-1 are the two single models. Check the specifications table on the product page for the detection range of each variant, and our sales team will confirm the per-variant minimum and lead time before you order.' ),
			array( 'id' => 'bvt7', 'q' => 'Can I mix different voltage tester models in one container order, and which of them ship from stock?', 'a' => 'Yes, we accept mixed-assortment container orders, so you can combine the 1AC-D, AC-1, the 1805A/B/C/D family and the EW001/EW002/EW003 testers in one shipment as long as each model meets its per-model minimum quantity. The 1AC-D and AC-1 are our steady stock models and leave Wenzhou faster than production-built units. For the per-model MOQ and lead time on slower SKUs such as the 1808, 1809 or 1888, confirm with our sales team before you finalize.' ),
			array( 'id' => 'bvt8', 'q' => 'Can you print my brand on the voltage testers, and do you offer both retail boxes and bulk packing?', 'a' => 'Yes, OEM printing is standard across this line. We print your logo and model marking on the 1AC-D, AC-1, 1805A/B/C/D and the EW series housings, and on the blister card or retail box. If you distribute to resellers rather than selling shelf-ready, we can ship the 1AC-D and AC-1 in bulk packaging without retail boxes. Send us your artwork and we will quote the packaging option, with CE and RoHS marking included on the label.' ),
		),
		'basic-digital-multimeters' => array(
			array( 'id' => 'bdm1', 'q' => 'What can a basic digital multimeter measure?', 'a' => 'It measures AC/DC voltage, AC/DC current and resistance — the essentials for home repairs, electronics work and basic electrical troubleshooting.' ),
			array( 'id' => 'bdm2', 'q' => 'Is a basic digital multimeter good enough for home use?', 'a' => 'Yes. For socket checks, appliance repairs and DIY projects, a basic multimeter covers the vast majority of jobs while staying affordable and easy to read.' ),
			array( 'id' => 'bdm3', 'q' => 'What is the accuracy of your basic multimeters?', 'a' => 'Accuracy varies by model — typically 0.5% to 1% for DC voltage. The exact spec is listed in each model\'s datasheet and specifications table.' ),
			array( 'id' => 'bdm4', 'q' => 'Do you sell basic multimeters in bulk for distributors?', 'a' => 'Yes, we are the factory. Distributors and wholesalers are welcome — send your target models and quantity for wholesale pricing and lead time.' ),
			array( 'id' => 'bdm5', 'q' => 'How do I choose between the DT830, DT838 and DM101 for my first wholesale order?', 'a' => 'For a first order, stock the DT830 or A830L as the entry price point and the DT838 or DM101 as the step-up tier. That gives you two price bands without tying up container space in slow SKUs, and you can add DT832, DT858L or XT802 later based on sell-through. If you are not sure which band sells in your market, order both in roughly equal quantities and reorder from the data. Compare the full functions on each model\'s specifications table before you fix the mix.' ),
			array( 'id' => 'bdm6', 'q' => 'I sell to auto-repair shops and also to hardware stores. Should I split my basic DMM order across different models?', 'a' => 'Most distributors run two tiers for this line: the DT830, DT830L and A830L for hardware and home channels, and the DT838, DT850L or DT858L for trade and auto customers who pay up for the step-up model. You can keep a single SKU to simplify stock, but splitting the order lets you match price bands to each channel. Tell us your expected split by customer type and we will quote per-model pricing for the full order.' ),
			array( 'id' => 'bdm7', 'q' => 'Can you print my own brand on the meter and box for the basic DMM range?', 'a' => 'Yes, we are the factory in Wenzhou, so we can print your brand on the meter face, the dial and the box for the basic DMM line, including DT830, DT832, DT838, DM101 and the rest of the range. For retail we offer printed color boxes with a fitted insert; for cost-sensitive programs we ship bulk packs with a plain carton and your label applied. Send us the models and packaging you want and we will confirm print minimums and artwork lead time for each model.' ),
			array( 'id' => 'bdm8', 'q' => 'If units in a bulk order arrive dead or fail during the warranty period, how do we claim?', 'a' => 'Return the defective meters with your order number and we will repair or replace them at our Wenzhou factory, and we check the same production lot so you can keep the remaining stock on the shelf with confidence. For wholesale orders we process claims by lot and can credit the replacement value against your next order to save on return freight. The process covers the whole basic DMM line, from the DT830L to the XT803.' ),
			array( 'id' => 'cap1', 'q' => 'Can a basic digital multimeter measure capacitance, or do I need a dedicated LCR meter?', 'a' => 'Most of our multimeters have a capacitance range, so you do not need a separate LCR meter for the everyday job of finding a failed capacitor. The XT802 covers 4 nF to 40 mF at ±10.8%, and the DT6222 covers 4 nF to 4 mF at ±3.5%. A bench LCR meter is still the better instrument if you need leakage, ESR or dissipation factor, because a multimeter only reports capacitance.' ),
			array( 'id' => 'cap2', 'q' => 'Why does my multimeter read a different capacitance than the value printed on the capacitor?', 'a' => 'The printed figure is a nominal value carrying its own tolerance, commonly ±5% for film and ±20% for electrolytics, so a small difference is normal and not a meter fault. A reading far outside that band usually means the capacitor has drifted or dried out rather than that the instrument is wrong. Check the meter against a capacitor of known value before you act on a surprising result.' ),
			array( 'id' => 'cap3', 'q' => 'Should I test a capacitor in circuit or desolder it first?', 'a' => 'Discharge it, then lift at least one leg before measuring. In circuit, everything connected in parallel with the capacitor adds to the reading, and any discharge path makes the value drift while you watch it. With one leg lifted the reading settles within a second or two, and that is the number worth recording.' ),
			array( 'id' => 'cap4', 'q' => 'What capacitance range do I need for typical repair work?', 'a' => 'Most repair work sits between 1 nF and 1000 µF: film and ceramic parts in the low nanofarad region, electrolytics in the microfarad region. The DT6222 covers 4 nF to 4 mF on a 4000-count display, so small values still have useful resolution. The XT802 spans 4 nF to 40 mF if your work reaches the large electrolytics as well.' ),
		),
		'digital-voltage-testers' => array(
			array( 'id' => 'dvt1', 'q' => 'How is a digital voltage tester different from a basic one?', 'a' => 'A digital voltage tester shows an actual voltage reading on a display, instead of only indicating "live or not". This helps you confirm the voltage level, not just that power is present.' ),
			array( 'id' => 'dvt2', 'q' => 'Can I use it to test AC outlets?', 'a' => 'Yes. Most of our digital voltage testers include outlet testing with a clear digital readout, so you can verify wiring quickly.' ),
			array( 'id' => 'dvt3', 'q' => 'Do these testers auto-range?', 'a' => 'Many models are auto-ranging — they select the right range automatically and show the reading. Check the model\'s specifications to confirm.' ),
			array( 'id' => 'dvt4', 'q' => 'Are digital voltage testers suitable for professional electricians?', 'a' => 'Yes, they combine speed with a precise readout, which makes them a practical choice for everyday electrical service work.' ),
			array( 'id' => 'dvt5', 'q' => 'Which voltage tester should we stock for our hardware retail counter versus our industrial wholesale channel?', 'a' => 'For a hardware or electrical supply counter, the 88 series (88-2, 88-4, 88-5, 88-6, 88-12) and the HW31x handheld line (HW311, HW312, HW313) are solid retail choices. For a wider stock list that serves both retail and wholesale customers, add 130805, 130855, MVD765 and WILK301 alongside them. We are the factory in Wenzhou, so send us your target channel and we will recommend the exact SKU mix for your first order.' ),
			array( 'id' => 'dvt6', 'q' => 'How do we choose between the HW311, HW312 and HW313 when placing our order?', 'a' => 'HW311, HW312 and HW313 are three handheld digital voltage testers in the same CHINYOO family, and the models differ in measurement range and feature set. Open the specifications table on each product page and compare the AC/DC range and any extra functions before you order. If the differences are not obvious to you, order a small first lot of two models and scale up the one that sells in your market.' ),
			array( 'id' => 'dvt7', 'q' => 'Can we mix several voltage tester models in one container, and what lead time should we plan for?', 'a' => 'Yes, mixed-assortment containers are normal for this line, and you can combine models such as 130805, 130855, 1881, BQMVT646, HW311, HW312, HW313, 6877-18 and 6877-58 in a single order. Most digital voltage tester SKUs are stock lines with short production lead time, and the exact timing depends on quantity and whether the units are bulk or custom-labeled. Send your SKU list and we will confirm the per-model MOQ and delivery date before you place the order.' ),
			array( 'id' => 'dvt8', 'q' => 'Can you print our own brand on the tester body and box for this voltage tester line?', 'a' => 'Yes, under an OEM agreement we can print your brand on the housing and packaging for the digital voltage tester line, including models such as 6877-18, 6877-58, 6885-48, SG5003A and SG8001. Packaging can be branded color boxes, blister packs, or bulk cartons depending on your channel. Per-model custom branding minimums apply, so send the SKU list and artwork, and we will confirm the order requirements.' ),
		),
		'digital-clamp-meters' => array(
			array( 'id' => 'dcm1', 'q' => 'What is a clamp meter used for?', 'a' => 'A clamp meter measures AC/DC current without breaking the circuit — you just clamp around a single conductor. It is the standard tool for motor, panel and machine current checks.' ),
			array( 'id' => 'dcm2', 'q' => 'Can a clamp meter also measure voltage and resistance?', 'a' => 'Yes, our digital clamp meters are multimeters too — they measure voltage, resistance, continuity and diode tests in addition to clamp current.' ),
			array( 'id' => 'dcm3', 'q' => 'What is the maximum current these clamp meters measure?', 'a' => 'Most of our models measure up to 400A or 600A AC/DC. The exact range is listed in each model\'s specifications table.' ),
			array( 'id' => 'dcm4', 'q' => 'How do I choose between a clamp meter and a multimeter?', 'a' => 'If you mainly measure current on live wires or motor circuits, choose a clamp meter. For general voltage and resistance testing, a multimeter is more compact. We offer both.' ),
			array( 'id' => 'dcm5', 'q' => 'I distribute to electricians and building contractors. Which clamp meter models should I stock first?', 'a' => 'For a general electrical trade market, the 266 family is the natural core of an assortment: the 266, XT266, XT266C and XT266FT share the 266 form factor and measure up to 1000A AC current. The VC3268 series (VC3268, VC3269, VC3270) is a separate catalog tier, and models like MT87, MT2010A and ST201 cover lighter and budget needs. We are the factory in Wenzhou, so share your customer segment and we will recommend an opening mix with the price tiers.' ),
			array( 'id' => 'dcm6', 'q' => 'When I look at your catalog, the XT266, XT266C and XT266FT look almost identical. How do I decide which ones to order?', 'a' => 'All three are part of the same 266 family, share the 266 form factor and measure up to 1000A AC current, so the difference is in added functions and the accessories in the box. The exact differences between XT266, XT266C and XT266FT are listed in the specifications table on each product page, and we can email a side-by-side comparison with your quotation. For a first order, start with the base XT266 and add the C and FT variants once your customers show which feature set they want.' ),
			array( 'id' => 'dcm7', 'q' => 'Can I mix different clamp meter models in one container order, and is there a per-model minimum?', 'a' => 'Yes, you can combine models like XT266, VC3270, MT2010A and DM6266F in a single container order, and we will quote each model separately. The per-model minimum depends on the packaging and accessory kit, so send us your target SKU mix and we will confirm the minimum for each. High-volume models are usually available from stock, while less common ones such as XT266FT or ST201 may be made to order, so confirm lead time when you place the order.' ),
			array( 'id' => 'dcm8', 'q' => 'What happens if some clamp meters arrive dead or fail in the first few months?', 'a' => 'Our clamp meters carry a 12-month warranty from the date of delivery. If units arrive defective or fail in use, send photos or a short video and we will confirm the fault; for a defective batch we arrange replacement or a credit against your next order. Because CHINYOO\'s factory is in Wenzhou, we can trace the production batch and keep spares for the 266 family and the VC3268 series, so claims are settled against the actual build, not through a middleman.' ),
		),
		'car-electric-pens' => array(
			array( 'id' => 'cep1', 'q' => 'What is a car electric pen used for?', 'a' => 'An automotive test pen checks 12V/24V car circuits — it traces power, ground and signals when diagnosing wiring faults on vehicles.' ),
			array( 'id' => 'cep2', 'q' => 'Does it work on both 12V and 24V vehicles?', 'a' => 'Yes, our car test pens support both 12V and 24V systems, covering cars, motorcycles, trucks and RVs.' ),
			array( 'id' => 'cep3', 'q' => 'How do I test a fuse with a car test pen?', 'a' => 'Touch the probe to each side of the fuse with the circuit on. If only one side lights, the fuse is blown. See the included manual for full testing steps.' ),
			array( 'id' => 'cep4', 'q' => 'Are these pens suitable for automotive professionals?', 'a' => 'Yes, they are a fast and reliable way to diagnose wiring faults and are widely used by mechanics and auto electricians.' ),
			array( 'id' => 'cep5', 'q' => 'Which car electric pen models should I stock if I mainly sell to retail auto parts stores rather than professional workshops?', 'a' => 'We are the factory in Wenzhou. For retail auto parts shelves we usually recommend the HW304, HW305 and HW306 family at three price points, plus HW80 as a compact basic pen. If your channel is professional workshops, HW170 and the HW68A/HW68B digital display pens are what we position for those customers, with WILK302 and WILK303 as a separate branded series. Tell us your target market and price range and we will suggest the exact mix for your first order.' ),
			array( 'id' => 'cep6', 'q' => 'What is the difference between HW68A and HW68B when I am deciding which to order?', 'a' => 'HW68A is the digital display car electric pen with a straight line lead, and HW68B is the same digital display design with a spring line lead. Both cover the same automotive testing role, so the choice comes down to which lead your customers prefer for reaching into a harness or cable. Check the specifications table on each product page for the exact voltage range and display features. We can mix both variants in one order so you can test sell-through before committing to a larger run.' ),
			array( 'id' => 'cep7', 'q' => 'Can I mix several car electric pen models in one container order, and do I need to reach a minimum quantity per model?', 'a' => 'Yes, you can mix models in a container order. We accept a mixed assortment across the car electric pen line, including HW170, HW304 to HW310, and WILK302 and WILK303, in one shipment. Each model still needs to reach its minimum quantity, so send us your quantity list and we will confirm the per-model MOQ and which items are in stock versus which need production time.' ),
			array( 'id' => 'cep8', 'q' => 'Can you print my brand on the car electric pens and put them in my own packaging?', 'a' => 'Yes. We are the factory in Wenzhou and we do OEM branding on the car electric pen line, printing your logo on models such as HW304 to HW310, HW170 and WILK302 and WILK303. You can choose a standard CHINYOO box, a plain bulk pack, or a custom box with your brand and artwork. Send us your logo and box design and we will confirm the printing and tooling cost before you order.' ),
			array( 'id' => 'fuse1', 'q' => 'How do I test a fuse without pulling it out?', 'a' => 'With the circuit powered, touch one probe to each of the two small test points on the top of the fuse. A good blade fuse shows the same voltage on both points; a blown fuse shows supply voltage on one side and nothing on the other. If the fuse has no test points, pull it and check continuity instead.' ),
			array( 'id' => 'fuse2', 'q' => 'What resistance should a good fuse read?', 'a' => 'Close to zero ohms, typically under 1 Ω, with the continuity buzzer sounding. A blown fuse reads open circuit, which most meters show as OL. Remember that the test leads add a fraction of an ohm of their own, so touch the probes together first and subtract that figure from what you see.' ),
			array( 'id' => 'fuse3', 'q' => 'Can I test a circuit breaker with a multimeter?', 'a' => 'Yes, with the circuit isolated. Switch the breaker off, prove the load side is dead with a contact tester, then measure continuity across the breaker terminals: a healthy closed breaker reads near zero and a tripped or failed one reads open. Never test a breaker with the circuit live, and treat a breaker that trips repeatedly as a circuit fault to be found rather than a breaker to be replaced.' ),
			array( 'id' => 'fuse4', 'q' => 'Why does a fuse show continuity but the circuit still does not work?', 'a' => 'Continuity proves only that the fuse element is intact. The usual causes are a corroded fuse holder, a fuse seated in the wrong slot, a relay or switch further down the circuit, or a second fuse on the same feed. Measure voltage at the fuse with the circuit on: if voltage arrives on one side and not the other, the fault is the contact rather than the element.' ),
		),
		'professional-digital-multimeters' => array(
			array( 'id' => 'pdm1', 'q' => 'What makes a professional digital multimeter different from a basic one?', 'a' => 'Professional models offer higher accuracy (up to 6000 counts), wider ranges, true-RMS AC measurement and more functions such as frequency and capacitance — built for demanding jobs.' ),
			array( 'id' => 'pdm2', 'q' => 'What is a 6000-count multimeter?', 'a' => 'A 6000-count display shows readings up to 5999, which means better resolution than a 2000-count meter — especially useful for detecting small voltage and current changes.' ),
			array( 'id' => 'pdm3', 'q' => 'Do professional multimeters have true RMS?', 'a' => 'Many of our professional models support true-RMS AC measurement, which gives accurate readings on non-sine wave signals found in modern equipment.' ),
			array( 'id' => 'pdm4', 'q' => 'Are your professional multimeters certified for industrial use?', 'a' => 'They are built to industrial standards with CE/RoHS compliance and safety-rated inputs. See each model\'s specifications for the exact safety and accuracy ratings.' ),
			array( 'id' => 'pdm5', 'q' => 'I sell to home DIY users and professional electricians. Which multimeter models should I stock in one order?', 'a' => 'For home and DIY demand, stock DT700 and MY62 as your entry-priced models. For electricians and industrial maintenance, add DT9205A, DT9208A, MY64, and VC890 to the mix. If your market also includes panel or precision work, VC9805 and VC9808 give you a higher-tier option. Tell us your customer segment and we will recommend a stock mix for the professional DMM line.' ),
			array( 'id' => 'pdm6', 'q' => 'Can I mix several multimeter models in one container, and is there a minimum per model?', 'a' => 'Yes, we allow mixed-assortment containers across the professional DMM line, for example DT9208A, DT9216, and VC17B+ in one shipment. Each model has its own wholesale MOQ, so confirm the per-model minimum with our sales team when you send the order. Stock models ship faster than made-to-order quantities, so lead time depends on which models you mix.' ),
			array( 'id' => 'pdm7', 'q' => 'Can you print my brand on the multimeters and pack them in plain bulk boxes?', 'a' => 'Yes, as the factory in Wenzhou we print your brand on the housing and on the box for the DT, MY, and VC series meters, including DM9205A, MY64, and VC9805. You can choose color box or plain bulk packing; bulk pack cuts freight and unit cost for container orders. Provide your artwork and packaging format when you place the order, and we will confirm before production.' ),
			array( 'id' => 'pdm8', 'q' => 'Which models in this line carry CE and RoHS, and can you provide the test reports?', 'a' => 'The professional DMM line, including DM9205A, DT700, DT9205A, DT9208A, DT9216, MY62, MY64, VC17B+, VC890, VC9805, and VC9808, is produced to meet CE and RoHS requirements. We provide the test reports and certificates for the specific models in your order. Send us your model list and we will confirm the documentation before you place the order.' ),
		),
		'inductive-alarm-testers' => array(
			array( 'id' => 'iat1', 'q' => 'What is an inductive alarm tester?', 'a' => 'It is a non-contact voltage detector that beeps and lights up when it senses an electric field — so you can verify a wire is live without touching it.' ),
			array( 'id' => 'iat2', 'q' => 'Is the alarm loud enough for noisy job sites?', 'a' => 'Yes, the tester combines a loud audible alarm with a bright LED indicator, so it can be seen and heard even on a busy site.' ),
			array( 'id' => 'iat3', 'q' => 'What voltage range does it detect?', 'a' => 'Most models detect AC voltage from around 12V to 1000V. The exact range is listed in the product specifications.' ),
			array( 'id' => 'iat4', 'q' => 'How do I check a wire is safe before working on it?', 'a' => 'Hold the tester near the wire or outlet — if it alarms, the circuit is live. Always test on a known-live source first to confirm the tester works, then test the target.' ),
			array( 'id' => 'iat5', 'q' => 'I sell into both US and EU markets. Will the same testers work on 120V and 230V mains, or do I need different models for each region?', 'a' => 'The same units work in both regions. All six models in the line, VD02, VD06, VD10, VD11, VD21 and VD123, are battery-powered non-contact detectors, so they never plug into the outlet and there is no plug or mains version to match. You differentiate between regions with the printed instructions language and packaging, not the hardware. For the AC detection range on each model, check the specifications table on the product page.' ),
			array( 'id' => 'iat6', 'q' => 'This is my first order for this line. Which two or three models should I stock to cover most of my customers?', 'a' => 'A common starting assortment is one compact entry model, VD02 or VD06, one middle option, VD10 or VD11, and one higher-featured unit, VD21 or VD123. That gives you three price points across the six-model line without carrying everything at once. For the exact differences in detection range, body size and buzzer output, check the specifications table on the product page, and we can send a mixed sample set so you can compare them before you order.' ),
			array( 'id' => 'iat7', 'q' => 'Can I mix several models in one container order, and is there a minimum quantity per model?', 'a' => 'Yes. You can combine VD02, VD06, VD10, VD11, VD21 and VD123 in a single mixed-assortment order, and we carton each model separately so your warehouse can match each carton to your own SKUs. The minimum per model and carton quantity differ across the line, so tell our sales team the split you have in mind and we will confirm before you place the order. We are the factory in Wenzhou, so stock models ship on the regular lead time and production runs are scheduled on our own line.' ),
			array( 'id' => 'iat8', 'q' => 'Can you print my own brand on the testers and put them in my own box for resale?', 'a' => 'Yes, all six models, VD02, VD06, VD10, VD11, VD21 and VD123, are available for private label. We can print your brand on the unit body and the box, and you can pick between color box and bulk carton packing for this line. Send us your logo and packaging design and we will confirm the printing method and any setup cost before production.' ),
			array( 'id' => 'ghost1', 'q' => 'Why does a non-contact voltage tester beep on a wire that has no power?', 'a' => 'Almost always capacitive coupling. A de-energised conductor running alongside a live one forms one plate of a capacitor with it, and the alternating field puts a real voltage on the floating conductor while the available current stays in the microamp range. A non-contact tester responds to that field rather than to current flowing, so it indicates even though the conductor cannot deliver power.' ),
			array( 'id' => 'ghost2', 'q' => 'How do I confirm that a reading is a ghost voltage and not a real one?', 'a' => 'Load the conductor deliberately. Use the low-impedance function on a multimeter if it has one, or connect a small load such as a filament lamp, and read the voltage again. A conductor held live by a supply keeps its voltage and drops only by what the load draws; a conductor held live by capacitive coupling collapses, because the coupling supplies microamps and the load asks for milliamps.' ),
			array( 'id' => 'ghost3', 'q' => 'What causes phantom voltage on a dead circuit?', 'a' => 'Long cable runs that parallel a live conductor, metal containment sitting at a potential, and the high input impedance of a digital meter all make it more likely. The effect grows with the length of the parallel run and with how closely the dead conductor follows the live one, which is why it turns up most often on long lighting circuits and on wiring installed in steel conduit.' ),
			array( 'id' => 'ghost4', 'q' => 'Is a non-contact tester safe for confirming a circuit is dead?', 'a' => 'Treat it as a first pass and never as the proof. Verify the tester on a known-live source, then on the circuit you are working on, then on the known-live source again, and confirm the dead verdict with a contact measurement made on the conductors themselves. Because a non-contact tester can be fooled in both directions, the rule is that proving the absence of voltage requires an instrument that makes contact.' ),
		),
		'battery-meters' => array(
			array( 'id' => 'bm1', 'q' => 'What does a battery meter do?', 'a' => 'A battery tester measures battery voltage and state of charge, so you can quickly tell whether a battery needs recharging or replacing.' ),
			array( 'id' => 'bm2', 'q' => 'Which batteries can it test?', 'a' => 'Our battery meters cover common household and automotive batteries, including 1.5V, 9V and 12V types. Check the specifications for the supported range.' ),
			array( 'id' => 'bm3', 'q' => 'Can it test car batteries?', 'a' => 'Yes, the 12V models are designed for car and motorcycle battery checks. They show the voltage and give a clear good, fair or recharge indication.' ),
			array( 'id' => 'bm4', 'q' => 'Is a battery meter easy to use?', 'a' => 'Very — you connect the test leads or place the battery in the slot, and the display shows the result immediately.' ),
			array( 'id' => 'bm5', 'q' => 'We are deciding between the BT-168 and BT-168D for our first order. What is the difference, and when should we stock each?', 'a' => 'The BT-168 is our classic analog needle battery tester, while the BT-168D gives a digital readout for a clearer numeric result. Stock the BT-168 as the low-cost SKU for a budget retail line, and the BT-168D for customers who want to read the value directly. The HW171 and HW172 sit above both in the line if you need wider coverage, and the exact test ranges are on each product page.' ),
			array( 'id' => 'bm6', 'q' => 'We supply hardware and home-improvement stores. Which battery meter should we stock for that channel?', 'a' => 'For a hardware and home retail channel, the BT-168D is the model most buyers order, because the digital display is easy for end consumers to read. The BT-168 works well as a low-cost entry SKU alongside it. If you also supply electronics or tool shops, add the HW171 and HW172 so you can cover customers who need a wider test range.' ),
			array( 'id' => 'bm7', 'q' => 'Can you print our brand on the battery meter and ship it in bulk cartons instead of retail blisters?', 'a' => 'Yes. As the CHINYOO factory in Wenzhou, we print your brand on the meter faceplate and the box for the BT-168, BT-168D, HW171 and HW172 under our OEM program. We can ship this line either in retail blister packs or in bulk cartons, so tell us your channel and we will confirm the packing spec. Send your artwork and we will verify the print area before you place the order.' ),
			array( 'id' => 'bm8', 'q' => 'Do the BT-168, BT-168D, HW171 and HW172 carry CE and RoHS, and can you provide the test reports?', 'a' => 'Yes, all four models in our battery meter line comply with CE and RoHS, and the CHINYOO factory holds ISO certification. We can provide the test reports for the BT-168, BT-168D, HW171 and HW172 before you order. Ask your sales contact for the current reports.' ),
			array( 'id' => 'bvc1', 'q' => 'What should a healthy 12 V car battery read with the engine off?', 'a' => 'A fully charged 12 V battery reads 12.65 V or above at rest. Below that, 12.45 V is roughly 75% charged, 12.24 V about 50%, 12.06 V about 25%, and 11.89 V or below is effectively flat. Measure across the battery terminals with the engine and all loads off, and let the battery stand before taking the reading.' ),
			array( 'id' => 'bvc2', 'q' => 'How do I check a car battery with a multimeter instead of a battery tester?', 'a' => 'Set the meter to DC volts on the 20 V range, put the red probe on the positive terminal and the black probe on the negative, and read the open-circuit voltage. That figure tells you the state of charge, not the capacity — a battery can show 12.6 V and still fail. Take the cranking reading as well, with the engine turning over.' ),
			array( 'id' => 'bvc3', 'q' => 'Why does my battery read 12.6 V but the car will not start?', 'a' => 'A good resting voltage only proves the battery is charged, not that it can deliver cranking current. A battery with a broken internal connection or heavily sulphated plates can hold a surface charge and still collapse the moment the starter draws current. Watch the voltage during cranking: if it falls below about 9.6 V the battery is the problem, and if it holds, look at the starter circuit and the connections.' ),
			array( 'id' => 'bvc4', 'q' => 'Do I need a load tester, or is a voltage reading enough?', 'a' => 'A voltage reading is enough to judge state of charge and it is the fastest check available. It cannot measure remaining capacity, so for a battery you suspect is failing under load you need either a load tester or the cranking-voltage test. Run both when a vehicle comes in with a no-start complaint and the resting voltage looks healthy.' ),
		),
		'socket-testers' => array(
			array( 'id' => 'st1', 'q' => 'What is a socket tester used for?', 'a' => 'A socket tester plugs into an AC outlet and checks the wiring in seconds — it detects correct wiring, reverse polarity, open ground and other faults.' ),
			array( 'id' => 'st2', 'q' => 'How long does it take to test an outlet?', 'a' => 'About 10-30 seconds per outlet: plug it in, read the LED pattern, and compare it with the legend printed on the tester.' ),
			array( 'id' => 'st3', 'q' => 'Does it check grounding?', 'a' => 'Yes, the LED indicator pattern shows whether the ground is properly connected, which is one of the most common wiring faults.' ),
			array( 'id' => 'st4', 'q' => 'Who should use a socket tester?', 'a' => 'Homeowners, inspectors and electricians all use it for a fast and safe way to verify outlet wiring before trusting a socket.' ),
			array( 'id' => 'st5', 'q' => 'I distribute in both Europe and North America. Which socket tester models should I stock, and how do the EU and US versions differ?', 'a' => 'We make the socket tester series in plug versions for the EU standard (230V) and the US standard (120V), so you can stock the version that matches each market. The versions differ in plug type and rated voltage, and each one checks wiring faults such as open ground, open neutral, open hot and reversed wiring for its own standard. For the exact plug and voltage coverage of each version, check the specifications table on the product page.' ),
			array( 'id' => 'st6', 'q' => 'Can you print our logo on the socket testers and pack them without retail boxes? What packaging options are available for this line?', 'a' => 'Yes. As the factory in Wenzhou, we can print your brand and artwork on the socket tester series under OEM. Packaging for this line is available as individual retail boxes or as bulk pack without retail boxes for distributors. Send us your logo and packaging design and we will confirm the printing and packing options for your order.' ),
			array( 'id' => 'st7', 'q' => 'Can I mix the EU-standard and US-standard socket testers in one container, and is there a minimum per version?', 'a' => 'Yes, we can mix the EU-standard and US-standard socket tester versions in a single container so you can cover both plug standards in one shipment. Each version in the line has its own minimum order quantity. Send us your target quantities and we will confirm the mix and per-version MOQ in the quote.' ),
			array( 'id' => 'st8', 'q' => 'Which socket tester models carry CE and RoHS, and can you send the test reports before I place an order?', 'a' => 'The socket tester series in the EU and US plug versions is CE and RoHS compliant, and CHINYOO is ISO 9001 certified. Our factory can send the certificates and test reports for the versions you are ordering. Ask for them together with your quote or samples and we will share the documents.' ),
		),
		'temperature-meters' => array(
			array( 'id' => 'tmp1', 'q' => 'Should I stock infrared thermometers or contact probe thermometers?', 'a' => 'Think first about what your customers measure most. Infrared models suit fast, no-contact surface checks like motors, food, or air vents, with nothing to clean between readings. Contact and K-type models suit readings where a probe matters, like liquids or inside equipment, and many technicians already own thermocouple probes. Most buyers stock one of each, and we can quote mixed loads so you cover both.' ),
			array( 'id' => 'tmp2', 'q' => 'Which CHINYOO temperature model should I offer for which application?', 'a' => 'Match the model to the job. HTC-2 and HTC-1 are compact thermometer-hygrometers for rooms, storage, and greenhouses, with a -10°C to +50°C range, 10-99% RH, 0.1°C resolution, ±2°C accuracy, alarm, and MAX/MIN. HT9269 reads several K-type thermocouple probes on one display, so it suits labs and maintenance teams that need multiple readings at once. GM320 is an infrared unit for fast surface checks, while HS-1, XT101, and XT300 cover general temperature meter needs. Full ranges and specs for every model are in the specifications table.' ),
			array( 'id' => 'tmp3', 'q' => 'Can I get samples first, and what are the MOQ and OEM options for the temperature line?', 'a' => 'Yes, samples are available before you commit, so your team can verify a unit against your own work. MOQ for the temperature line is modest and negotiable per model, and mixed-model first orders are welcome. We also run OEM and private label, with your logo, packaging, and labeling handled at our Wenzhou factory. Tell us the models and quantities you plan, and we will send a sample quote with wholesale pricing.' ),
			array( 'id' => 'tmp4', 'q' => 'What are the lead times, shipping options, and CE RoHS certification for the temperature line?', 'a' => 'Lead time depends on quantity and stock, and we confirm a concrete date in your written quote so there are no surprises. We ship by courier for samples and small orders, and by sea or air freight for bulk. The temperature line carries CE and RoHS certification, and we include the certificate documents as proof with each order. Send the models and destination, and we will reply with lead time, freight options, and certificate copies.' ),
		),
	);
}

/**
 * 当前语言翻译 dict(仅非 EN)。id => 译文文本。
 */
function ew_geo_faq_dict() {
	$lang = function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';
	if ( 'en' === $lang ) {
		return array();
	}
	static $dicts = null;
	if ( null === $dicts ) {
		$dicts = array( 'zh' => array(), 'es' => array(), 'ru' => array(), 'ar' => array() );
		foreach ( array_keys( $dicts ) as $l ) {
			$file = __DIR__ . '/geo-faq-strings-' . $l . '.php';
			if ( file_exists( $file ) ) {
				$fn = 'ew_geo_faq_dict_' . $l;
				require_once $file;
				if ( function_exists( $fn ) ) {
					$dicts[ $l ] = $fn();
				}
			}
		}
	}
	return isset( $dicts[ $lang ] ) ? $dicts[ $lang ] : array();
}

/**
 * 当前语言下某分类的问答列表。
 *
 * @param string $category_key general 或 ew_geo_faq_categories() 的键。
 * @return array[] 每个元素 ['q'=>, 'a'=>]
 */
function ew_geo_faq( $category_key ) {
	$all  = ew_geo_faq_all();
	$dict = ew_geo_faq_dict();
	$list = isset( $all[ $category_key ] ) ? $all[ $category_key ] : array();
	$out  = array();
	foreach ( $list as $qa ) {
		$q = $qa['q'];
		$a = $qa['a'];
		if ( isset( $dict[ $qa['id'] . 'q' ] ) && '' !== $dict[ $qa['id'] . 'q' ] ) {
			$q = $dict[ $qa['id'] . 'q' ];
		}
		if ( isset( $dict[ $qa['id'] . 'a' ] ) && '' !== $dict[ $qa['id'] . 'a' ] ) {
			$a = $dict[ $qa['id'] . 'a' ];
		}
		$out[] = array( 'q' => $q, 'a' => $a );
	}
	return $out;
}

/**
 * 解析产品 → FAQ 分类键。优先命中 9 个主分类；否则父类兜底；再否则 general。
 */
function ew_geo_faq_for_product( $product_id ) {
	$primary    = ew_geo_faq_categories();
	$parent_map = array(
		'digital-multimeters' => 'basic-digital-multimeters',
		'voltage-testers'     => 'basic-voltage-testers',
		'clamp-meters'        => 'digital-clamp-meters',
	);
	/* 按产品 slug 精准路由(温度仪表挂在 others 分类,需专用 FAQ 块) */
	$slug_map = array(
		'gm320'                    => 'temperature-meters',
		'hs-1-temperature-meter'   => 'temperature-meters',
		'ht9269-multi-thermometer' => 'temperature-meters',
		'htc-1-temperature-meter'  => 'temperature-meters',
		'htc-2-temperature-meter'  => 'temperature-meters',
		'xt101-temperature-meter'  => 'temperature-meters',
		'xt300-temperature-meter'  => 'temperature-meters',
	);
	$p_slug = get_post_field( 'post_name', (int) $product_id );
	if ( isset( $slug_map[ $p_slug ] ) ) {
		return $slug_map[ $p_slug ];
	}
	$terms = get_the_terms( (int) $product_id, 'product_cat' );
	if ( ! is_array( $terms ) || is_wp_error( $terms ) || empty( $terms ) ) {
		return 'general';
	}
	$slugs = array();
	foreach ( $terms as $t ) {
		// 注意:pll_get_term 第二参传整型 term_id(传对象会返回 0)
		$t_id  = (int) $t->term_id;
		$en_id = function_exists( 'pll_get_term' ) ? pll_get_term( $t_id, 'en' ) : $t_id;
		$en_id = $en_id ? (int) $en_id : $t_id;
		$en_t  = get_term( $en_id, 'product_cat' );
		$slug  = ( $en_t && ! is_wp_error( $en_t ) ) ? $en_t->slug : $t->slug;
		if ( $slug ) {
			$slugs[] = $slug;
		}
	}
	foreach ( $slugs as $s ) {
		if ( isset( $primary[ $s ] ) ) {
			return $s;
		}
	}
	foreach ( $slugs as $s ) {
		if ( isset( $parent_map[ $s ] ) ) {
			return $parent_map[ $s ];
		}
	}
	return 'general';
}

/* =====================================================================
 * 文章页 FAQ (GEO) — /news/ 单篇底部可见问答 + FAQPage JSON-LD。
 * 主题 = 文章类型(选购指南/对比/新品/公司)，按 EN 文章 slug 解析。
 * EN 源在此；翻译在 inc/news-faq-strings-{zh,es,ru,ar}.php。
 * ===================================================================== */

/** EN 文章 slug → 主题键。 */
function ew_news_faq_topics() {
	return array(
		'how-to-choose-a-digital-multimeter'    => 'dmm-choose',
		'dt700-digital-multimeter-guide'        => 'dt700-guide',
		'clamp-meter-vs-digital-multimeter'     => 'clamp-vs-mm',
		'voltage-testers-guide-types-and-safety' => 'vt-guide',
		'automotive-electrical-testing-guide'   => 'auto-testing',
		'home-wiring-safety-socket-tester'      => 'socket-home',
		'battery-and-temperature-testers-guide' => 'battery-temp',
		'new-6000-count-multimeter-series'      => 'product-news',
		'meet-chinyoo-20-years'                 => 'company',
		'chinyoo-exhibits-bangkok-trade-fair-2026' => 'company',
		'266-series-digital-clamp-meter'        => 'clamp-266',
		'ht9269-multi-channel-thermometer-guide' => 'ht9269-guide',
		'htc-2-temperature-humidity-meter-guide' => 'htc2-guide',
		'factory-vs-trading-company-buy-digital-multimeters' => 'factory-trading',
		'cat-ratings-explained'                 => 'cat-ratings',
		'how-to-choose-a-clamp-meter'           => 'clamp-choose',
		'multimeter-symbols-guide'              => 'symbols',
		'choose-multimeter-manufacturer-china'  => 'manufacturer',
		'how-to-use-a-multimeter'               => 'mm-howto',
		'infrared-thermometer-guide'            => 'ir-guide',
		'outlet-tester-guide'                   => 'outlet-guide',
		'multimeter-for-hvac-guide'             => 'hvac-guide',
		'clamp-meter-how-to-use'                => 'clamp-howto',
		'dt830-digital-multimeter-guide'        => 'dt830-guide',
		'voltage-tester-vs-multimeter'          => 'vt-vs-mm',
		'multimeter-for-automotive-guide'       => 'auto-mm-buy',
		'1881-digital-inductive-voltage-tester' => 'iat-1881',
		'dt9205a-digital-multimeter-guide' => 'dt9205-guide',
		'6877-58-induction-voltage-tester-guide' => 'vt-guide',
		'vc3268-digital-clamp-meter-guide' => 'vc3268-guide',
		'6885-48-digital-voltage-tester-guide' => 'vt-guide',
		'a830l-digital-multimeter-guide' => 'a830l-guide',
		'88-2-voltage-test-pen-guide' => '88-2-guide',
		'dm6266-digital-clamp-meter-guide' => 'dm6266-guide',
		'mvd765-series-digital-voltage-tester-guide' => 'mvd765-guide',
		'multimeter-with-temperature-guide' => 'temp-dmm-guide',
		'vc9805-digital-multimeter-guide' => 'vc9805-guide',
		'st105-digital-multimeter-guide' => 'st105-guide',
		'bt-168-battery-tester-guide' => 'bt168-guide',
		'vc890-digital-multimeter-guide' => 'vc890-guide',
	
    'dt33-digital-multimeter-guide' => 'dt33-guide',
	'how-to-test-a-capacitor-with-a-multimeter' => 'cap-guide',
	'car-battery-voltage-chart' => 'bvc-guide',
	'test-a-fuse-and-circuit-breaker-with-a-multimeter' => 'fuse-guide',
	'why-non-contact-voltage-tester-beeps-on-dead-wire' => 'ghost-guide',
	// 批次 5（2026-10-15）：通断读数 / 电流插孔 / 暗电流。
	'how-to-test-continuity-with-a-multimeter' => 'continuity-reading',
	'multimeter-current-jack-10a-vs-ma' => 'current-jack',
	'car-battery-parasitic-draw-test' => 'parasitic-draw',
	// 批次 6（2026-10-29）：价格构成 / GFI 与 GFCI / 电工选表。
	'multimeter-price-factory-vs-retail' => 'price-anatomy',
	'gfi-vs-gfci' => 'gfi-gfci',
	'multimeter-for-electricians' => 'electrician-spec',
);
}

/** EN 源新闻问答。id 前缀：dmm/cmm/vt/at/sh/bt/pn/co。 */
function ew_news_faq_all() {
	return array(
		'dmm-choose' => array(
			array( 'id' => 'dmm1', 'q' => 'What are the most important specs to check when buying a digital multimeter?', 'a' => 'Start with the display count (2000 vs 6000), accuracy, auto-ranging, safety category rating and the measurement functions you actually need. For most household and maintenance jobs a basic 2000-count meter is enough; for finer readings choose a 6000-count model.' ),
			array( 'id' => 'dmm2', 'q' => 'Do I need auto-ranging?', 'a' => 'Auto-ranging selects the correct range for you automatically, which is faster and safer, especially for beginners. Manual-ranging meters give more control but require you to set the range yourself.' ),
			array( 'id' => 'dmm3', 'q' => 'How many counts do I need?', 'a' => 'A 2000-count display suits general household and maintenance work. A 6000-count display shows smaller voltage changes and is preferred for electronics and precision work.' ),
			array( 'id' => 'dmm4', 'q' => 'What is the difference between a basic and a professional multimeter?', 'a' => 'Professional models add true-RMS AC measurement, higher accuracy, wider ranges and extra functions such as frequency and capacitance. They cost more but deliver more for demanding jobs.' ),
		),
		'dt700-guide' => array(
			array( 'id' => 'dt7001', 'q' => 'Is the DT700 a professional digital multimeter?', 'a' => 'The DT700 series is a professional-grade pocket multimeter with ±0.5% DC voltage accuracy, overload protection and measurement up to 1000V and 10A. It is the compact workhorse for technicians who need reliable readings without the bulk of a bench meter.' ),
			array( 'id' => 'dt7002', 'q' => 'Does the DT700 measure temperature?', 'a' => 'Yes. The DT700C model measures temperature from -20°C to 1000°C with ±2.0% accuracy using the thermocouple probe included in the box, so it works as both a multimeter and a temperature meter for HVAC and appliance service.' ),
			array( 'id' => 'dt7003', 'q' => 'What is the difference between DT700C, DT700B and DT700D?', 'a' => 'All three models share the same ranges and ±0.5% DCV accuracy. DT700C adds temperature measurement, DT700D adds a 50Hz square-wave output, and DT700B is the entry model. Choose by the extra function your work needs.' ),
			array( 'id' => 'dt7004', 'q' => 'Can I buy the DT700 in bulk or with my own brand?', 'a' => 'Yes — CHINYOO is the factory. We supply DT700 series meters for wholesale and OEM/ODM programs with custom branding, packaging and documentation including CE and RoHS. Send us your target models and quantity for a quotation.' ),
		),
		'clamp-vs-mm' => array(
			array( 'id' => 'cmm1', 'q' => 'Which is better, a clamp meter or a digital multimeter?', 'a' => 'Neither is universally better. A clamp meter measures current without breaking the circuit and is ideal for motors and distribution panels. A multimeter is more compact for voltage, resistance and continuity checks.' ),
			array( 'id' => 'cmm2', 'q' => 'Can a clamp meter measure everything a multimeter can?', 'a' => 'Most digital clamp meters are also multimeters, measuring voltage, resistance, continuity and diode tests in addition to clamp current. Some budget clamp meters have fewer ranges, so check the specifications.' ),
			array( 'id' => 'cmm3', 'q' => 'When do I need to measure current with a clamp?', 'a' => 'When you must measure current on a live conductor without disconnecting it — typical for motor loads, distribution panels, appliances and machine maintenance.' ),
			array( 'id' => 'cmm4', 'q' => 'Which should I choose for automotive work?', 'a' => 'For diagnosing vehicle circuits, a multimeter or a car test pen is usually the better fit. A clamp meter helps when you need starter or alternator current. Contact us and we will recommend a model.' ),
		),
		'vt-guide' => array(
			array( 'id' => 'vt1', 'q' => 'What is the difference between a contact and a non-contact voltage tester?', 'a' => 'A contact tester must touch the conductor. A non-contact (inductive) tester detects the electric field nearby without touching it, which is ideal for a fast "is it live" check.' ),
			array( 'id' => 'vt2', 'q' => 'Are voltage testers safe to use?', 'a' => 'Yes, when used correctly. Our CE/RoHS-compliant models have insulated housings, and you should always confirm the tester works on a known-live source before and after each use.' ),
			array( 'id' => 'vt3', 'q' => 'When should I use a voltage tester instead of a multimeter?', 'a' => 'When you only need a yes or no "is this live" answer, a voltage tester is faster, simpler and cheaper. Use a multimeter when you need an actual voltage reading.' ),
			array( 'id' => 'vt4', 'q' => 'Do you sell both AC and DC voltage testers?', 'a' => 'Yes — our voltage tester range covers AC/DC, digital-readout and non-contact models for both household and professional work.' ),
		),
		'auto-testing' => array(
			array( 'id' => 'at1', 'q' => 'What does an automotive test pen check?', 'a' => 'It traces power, ground and signals in 12V/24V vehicle circuits to help you find blown fuses, broken wires and faulty components.' ),
			array( 'id' => 'at2', 'q' => 'Does a car test pen work on both 12V and 24V systems?', 'a' => 'Yes, our car electric pens cover 12V and 24V, so they suit cars, motorcycles, trucks and RVs.' ),
			array( 'id' => 'at3', 'q' => 'How do I find a blown fuse with a test pen?', 'a' => 'With the circuit on, touch the probe to both sides of each fuse. If only one side lights, that fuse is blown and should be replaced.' ),
			array( 'id' => 'at4', 'q' => 'Can I use a regular multimeter for car wiring instead?', 'a' => 'Yes, a digital multimeter also works for automotive voltage and continuity checks. Choose the tool that fits the job — we sell both.' ),
		),
		'socket-home' => array(
			array( 'id' => 'sh1', 'q' => 'What can a socket tester detect?', 'a' => 'It checks an AC outlet in seconds — correct wiring, reverse polarity, open ground, open neutral and crossed wires.' ),
			array( 'id' => 'sh2', 'q' => 'How do I use a socket tester?', 'a' => 'Plug it into the outlet and compare the LED pattern with the legend printed on the tester. Each pattern indicates a specific wiring condition.' ),
			array( 'id' => 'sh3', 'q' => 'Do I need an electrician if the tester shows a fault?', 'a' => 'If the tester shows anything other than "correct", stop using that outlet and have a qualified electrician fix the wiring — especially for ground or polarity faults.' ),
			array( 'id' => 'sh4', 'q' => 'Can a socket tester replace a multimeter?', 'a' => 'For outlet wiring checks, a socket tester is faster and needs no skill to read. A multimeter gives more detail but takes longer and requires more knowledge.' ),
		),
		'battery-temp' => array(
			array( 'id' => 'bt1', 'q' => 'What does a battery tester do?', 'a' => 'It measures battery voltage and state of charge to tell you whether a battery needs recharging or replacing.' ),
			array( 'id' => 'bt2', 'q' => 'Which batteries can you test?', 'a' => 'Our battery meters cover common household batteries (1.5V and 9V) and automotive 12V batteries, depending on the model. Check the specifications for the supported range.' ),
			array( 'id' => 'bt3', 'q' => 'What are temperature meters used for?', 'a' => 'They measure surface and ambient temperature for HVAC, appliance and maintenance checks, making them a practical companion to your electrical tools.' ),
			array( 'id' => 'bt4', 'q' => 'Do you sell battery and temperature testers for professionals?', 'a' => 'Yes, our range covers basic and professional models with clear displays and fast readings.' ),
		),
		'product-news' => array(
			array( 'id' => 'pn1', 'q' => 'What is new in the 6000-count series?', 'a' => 'The new series offers up to 6000 counts, true-RMS AC measurement and higher accuracy, built for professional and precision work.' ),
			array( 'id' => 'pn2', 'q' => 'When is the 6000-count series available?', 'a' => 'The series is available to order now. Contact our sales team for pricing, lead time and datasheets.' ),
			array( 'id' => 'pn3', 'q' => 'What makes a 6000-count display better?', 'a' => 'It shows readings up to 5999, giving finer resolution than a 2000-count meter — useful for detecting small voltage and current changes.' ),
			array( 'id' => 'pn4', 'q' => 'Can I order samples before buying in bulk?', 'a' => 'Yes, we welcome sample orders so you can evaluate the quality before committing to a bulk order.' ),
		),
		'clamp-266' => array(
			array( 'id' => 'cl2661', 'q' => 'What is the maximum current the 266 series clamp meter measures?', 'a' => 'Up to 1000 A AC on the 200 A and 1000 A clamp ranges — enough for motor starters, distribution feeders and heavy appliance loads, measured without breaking the circuit.' ),
			array( 'id' => 'cl2662', 'q' => 'Can the 266 series measure voltage and resistance?', 'a' => 'Yes. It measures DC voltage to 1000 V, AC voltage to 750 V and resistance to 2000 MΩ, with a continuity buzzer and diode test — a full multimeter around the clamp.' ),
			array( 'id' => 'cl2663', 'q' => 'Which 266 model should I choose?', 'a' => 'Start with the 266 for general industrial work. The XT266C adds non-contact temperature measurement for HVAC and thermal checks, and the XT2017 offers 600 V rated ranges. Tell us your typical measurements and we will recommend a model.' ),
			array( 'id' => 'cl2664', 'q' => 'Can I buy the 266 series in bulk or with my own brand?', 'a' => 'Yes — CHINYOO is the manufacturer. We supply 266 series clamp meters for wholesale and OEM/ODM programs with custom branding, packaging and CE/RoHS documentation.' ),
		),
		'company' => array(
			array( 'id' => 'co1', 'q' => 'Where is CHINYOO located?', 'a' => 'We are a factory in Wenzhou, Zhejiang — China\'s electrical hardware capital — founded in 2008 to manufacture measuring instruments.' ),
			array( 'id' => 'co2', 'q' => 'Do you do OEM/ODM?', 'a' => 'Yes, we offer OEM/ODM services with custom labels, packaging and specifications. Send us your requirements and we will confirm the details and pricing.' ),
			array( 'id' => 'co3', 'q' => 'What certifications do you have?', 'a' => 'Our instruments are CE and RoHS compliant and our factory is ISO 9001 certified. Certificates are available on request.' ),
			array( 'id' => 'co4', 'q' => 'How do I get a quotation?', 'a' => 'Send us the models and quantities you need through the contact form or by email, and our sales team will reply with pricing and lead time within 24 hours.' ),
		),
		'ht9269-guide' => array(
			array( 'id' => 'h92g1', 'q' => 'What is the HT9269 multi-channel thermometer used for?', 'a' => 'The HT9269 reads several K-type thermocouple probes at the same time on one display, so you can compare temperatures at different points — supply and return air, motor bearings, oven zones — without reconnecting sensors. It is used in HVAC, laboratory and industrial temperature work.' ),
			array( 'id' => 'h92g2', 'q' => 'What temperature range does the HT9269 support?', 'a' => 'The HT9269 accepts standard K-type thermocouple probes, which cover roughly −200 °C to +1300 °C depending on the probe construction. Contact us and we will confirm the exact range and probe options for your application.' ),
			array( 'id' => 'h92g3', 'q' => 'Can I use any K-type probe with the HT9269?', 'a' => 'Yes. K-type is an industry-standard thermocouple calibration, so compatible probes are easy to source and replace. A damaged probe is swapped in seconds instead of sending the meter for repair.' ),
			array( 'id' => 'h92g4', 'q' => 'Does CHINYOO support OEM/ODM for the HT9269?', 'a' => 'Yes — we are the factory. We supply HT9269 meters for wholesale and OEM/ODM programs with custom branding, packaging and CE/RoHS documentation. Send us your target models and quantity for a quotation.' ),
		),
		'htc2-guide' => array(
			array( 'id' => 'h2g1', 'q' => 'What is the difference between the HTC-2 and HTC-1?', 'a' => 'Both measure room temperature (−10 °C to +50 °C) and relative humidity (10%–99% RH). The HTC-2 adds an alarm and MAX/MIN memory for recalling the highest and lowest readings; the HTC-1 offers a calendar with a 12/24-hour clock display.' ),
			array( 'id' => 'h2g2', 'q' => 'How accurate is the HTC-2 thermometer hygrometer?', 'a' => 'Temperature accuracy is ±2 °C with 0.1 °C resolution, and humidity resolution is 1%. That is sufficient for indoor comfort monitoring in homes, offices, greenhouses and warehouses.' ),
			array( 'id' => 'h2g3', 'q' => 'Does the HTC-2 measure both temperature and humidity?', 'a' => 'Yes, it shows both on the display: temperature from −10 °C to +50 °C and relative humidity from 10% to 99% RH, plus time, alarm and MAX/MIN memory.' ),
			array( 'id' => 'h2g4', 'q' => 'Can I buy the HTC-2 in bulk with my own brand?', 'a' => 'Yes — CHINYOO is the manufacturer. We supply HTC-2 units for wholesale and OEM/ODM programs with custom branding, packaging and CE/RoHS documentation.' ),
		),
		'factory-trading' => array(
			array( 'id' => 'ft1', 'q' => 'How do I verify a Chinese multimeter factory is real without visiting?', 'a' => 'Ask for the production address and check it on a map, request fresh line photos or a video call with today\'s date on a whiteboard, talk specifications beyond price, verify their CE/RoHS/ISO certificates with the issuing body, and request a small custom change — real factories can show production and accommodate changes.' ),
			array( 'id' => 'ft2', 'q' => 'What is the minimum order quantity for digital multimeters?', 'a' => 'MOQ depends on the model and configuration. Standard models like the DT830 series are more flexible than custom OEM versions. Tell us your quantity and target price and we will quote accordingly.' ),
			array( 'id' => 'ft3', 'q' => 'Can a trading company offer OEM multimeters with my brand?', 'a' => 'Usually not in practice. OEM work — printing your logo, custom packaging, range changes — is routine for a factory because it controls the production line and tooling. A trading company generally cannot make changes because the factory behind it will not cooperate.' ),
			array( 'id' => 'ft4', 'q' => 'What certifications should a factory supplier provide for multimeters?', 'a' => 'CE (EMC · LVD) and RoHS are the baseline, ideally under an ISO 9001 quality management system. Ask for the certificate documents and verify the certificate number with the issuing body before ordering in volume.' ),
		),
		'cat-ratings' => array(
			array( 'id' => 'cr1', 'q' => 'What does a CAT rating on a multimeter actually mean?', 'a' => 'The CAT (measurement category) rating from IEC 61010 describes how much transient overvoltage the meter can withstand when connected to a circuit in that category. A higher category — CAT IV is the highest — means the meter is built to survive bigger transients safely.' ),
			array( 'id' => 'cr2', 'q' => 'What is the difference between CAT II, CAT III and CAT IV?', 'a' => 'CAT II covers circuits fed from a wall outlet, like appliances and tools. CAT III covers distribution-level work — panels, motor controls, fixed wiring — which is where most industrial and maintenance work happens. CAT IV is the service entrance where the grid enters the building, with the highest transient exposure.' ),
			array( 'id' => 'cr3', 'q' => 'Can I use a CAT II multimeter on a distribution panel?', 'a' => 'Technically the meter will measure, but you would be using it beyond its designed safety category. Panels and motor controls are CAT III environments, so choose a meter rated CAT III 600 V or higher with input protection for that work.' ),
			array( 'id' => 'cr4', 'q' => 'How do I check a multimeter\'s CAT rating is genuine?', 'a' => 'Ask the manufacturer for the IEC 61010 test report and the CE EMC/LVD documentation behind the rating. Meters with exaggerated or untested ratings are one of the most common faults in cheap imports, so buy from a factory that can document compliance.' ),
		),
		'clamp-choose' => array(
			array( 'id' => 'cc1', 'q' => 'Do I need an AC/DC clamp meter or is AC-only enough?', 'a' => 'For most power-distribution work — motor loads, heaters, feeders — an AC-only clamp is enough. If you measure DC current in solar installations, automotive, battery systems or DC drives, choose an AC/DC model with a Hall-effect sensor. Our clamp meter range includes both.' ),
			array( 'id' => 'cc2', 'q' => 'What is True RMS and do I need it on a clamp meter?', 'a' => 'On clean sinusoidal loads average-responding and True RMS meters read the same. On modern equipment — variable frequency drives, switching power supplies, electronic ballasts — an average-responding meter can read low. True RMS models compute the actual heating value of the waveform and are the right choice for industrial and commercial buyers.' ),
			array( 'id' => 'cc3', 'q' => 'What jaw size do I need for a clamp meter?', 'a' => 'Jaws must open wide enough to fit the conductor. Small-jaw models suit panels and control cabinets; larger jaws (40 mm and up) are for thicker feeder cables and bus bars. If your customers work on generators and heavy equipment, prioritize jaw capacity over portability.' ),
			array( 'id' => 'cc4', 'q' => 'Can I stock clamp meters under my own brand?', 'a' => 'Yes. Popular lines such as the XT2017, VC3268 and VC3269 / VC3270 series are all available with your branding under an OEM program, with a compact AC clamp, an AC/DC clamp and a large-jaw industrial model as the typical distributor mix.' ),
		),
		'symbols' => array(
			array( 'id' => 'sy1', 'q' => 'What does the V~ symbol on a multimeter mean?', 'a' => 'V~ is AC voltage and V⎓ is DC voltage. Set the dial to the matching symbol for the signal you are measuring — most meters also have a mV position for low-voltage sensor work. Always move the test lead to the correct jack first.' ),
			array( 'id' => 'sy2', 'q' => 'What does the continuity symbol ())) on a multimeter mean?', 'a' => 'The continuity symbol (often shown as ))) ) beeps when the circuit is closed, so you can trace wires and check a circuit is intact without reading numbers. It is the fastest way to find an open or shorted path.' ),
			array( 'id' => 'sy3', 'q' => 'Why does my multimeter show 1 on the left side of the display?', 'a' => 'A "1" on the left is the overload / out-of-range indicator. It means the reading exceeds the current range — switch to a higher range, or with autoranging meters select the function again. In resistance mode it usually means an open circuit.' ),
			array( 'id' => 'sy4', 'q' => 'Do CHINYOO multimeters include a temperature symbol?', 'a' => 'Some models do. The DT700C pocket multimeter, for example, measures temperature from −20 °C to 1000 °C with a thermocouple probe, shown on the dial with the °C position. Check the specifications of each model for the functions it offers.' ),
		),
		'manufacturer' => array(
			array( 'id' => 'mf1', 'q' => 'How do I know if a Chinese multimeter supplier is a factory or a trading company?', 'a' => 'Ask for the production address and check it on a map, request fresh photos or a video call of the line, talk real specifications, verify certificates with the issuing bodies, and request a small custom change. A factory can show production and accommodate changes; a trading company usually cannot.' ),
			array( 'id' => 'mf2', 'q' => 'What is a realistic MOQ for OEM multimeters?', 'a' => 'MOQ depends on the model and the level of customization. Standard stock models are more flexible and can often be ordered in small trial batches; OEM versions with custom branding and packaging carry a higher minimum. Tell us your target model and quantity and we will quote.' ),
			array( 'id' => 'mf3', 'q' => 'What should I verify before placing a bulk multimeter order?', 'a' => 'Run the checklist: production base, certifications (CE EMC/LVD, RoHS, ISO 9001), customization options and their MOQs, quality control — how many units are tested per batch — a written 12-month warranty, and a sample order before committing to a container.' ),
			array( 'id' => 'mf4', 'q' => 'How long does an OEM multimeter order take from sample to shipment?', 'a' => 'After you approve a sample, production lead time depends on the order size and how much is customized. Standard configurations ship faster; custom branding and packaging add a little time. Ask our sales team and we will give you a schedule for your specific order.' ),
		),
		'mm-howto' => array(
			array( 'id' => 'mmh1', 'q' => 'What is the safest way to measure AC voltage with a multimeter?', 'a' => 'Set the dial to the AC voltage position, plug black into COM and red into the voltage jack, pick a range above the expected voltage, and touch the probes to the terminals. Never switch the dial position while the probes are on a live circuit.' ),
			array( 'id' => 'mmh2', 'q' => 'Why does my multimeter show 1 on the left of the display?', 'a' => 'A "1" on the left is the out-of-range indicator. It means the reading is above the selected range, so switch to a higher range rather than assuming the value is real.' ),
			array( 'id' => 'mmh3', 'q' => 'Can I measure current with a regular multimeter?', 'a' => 'Yes, but the meter must be wired in series with the load, which means opening the circuit. For current on live motors and panels, a clamp meter is safer and faster because the jaws close around the conductor without breaking anything.' ),
			array( 'id' => 'mmh4', 'q' => 'Which multimeter is best for a beginner?', 'a' => 'A basic meter like the DT830 series covers voltage, resistance and continuity checks for most household jobs. If you work on live circuits, choose a meter with a CAT safety rating and learn to use a clamp meter for current.' ),
		),
		'ir-guide' => array(
			array( 'id' => 'irg1', 'q' => 'What does an infrared thermometer actually measure?', 'a' => 'It measures the surface temperature of what is inside its measurement spot. Not the air, and not the inside of the object, so distance, spot size and emissivity all change the reading.' ),
			array( 'id' => 'irg2', 'q' => 'Why does my infrared thermometer read low on metal?', 'a' => 'Polished metal reflects background heat instead of radiating it predictably. Put a patch of black tape or matte paint on the target and aim at the patch for a reliable reading.' ),
			array( 'id' => 'irg3', 'q' => 'What is the distance-to-spot ratio?', 'a' => 'It describes how big the measurement spot grows with distance. At 12:1, a target 12 inches away is measured over a roughly 1-inch circle. Work close and hold the trigger until the reading settles.' ),
			array( 'id' => 'irg4', 'q' => 'Which temperature instruments does CHINYOO offer?', 'a' => 'Besides spot-check infrared models, the factory builds multi-channel thermometers with probes, temperature and humidity meters, and temperature testers for panel and bench use. Contact us for the current range.' ),
		),
		'outlet-guide' => array(
			array( 'id' => 'otg1', 'q' => 'What does an outlet tester detect?', 'a' => 'It plugs into an AC socket and reads the LED pattern to check correct wiring, reverse polarity, open ground, open neutral and crossed wires in seconds.' ),
			array( 'id' => 'otg2', 'q' => 'How do I use an outlet tester?', 'a' => 'Plug it into the outlet, read the LED pattern, and compare it with the legend printed on the tester. Press the GFCI test button to verify the safety device trips and resets.' ),
			array( 'id' => 'otg3', 'q' => 'Do I need an electrician if the tester shows a fault?', 'a' => 'Yes. If the pattern is anything other than correct wiring, stop using that outlet and have a qualified electrician fix it, especially for ground or polarity faults.' ),
			array( 'id' => 'otg4', 'q' => 'Does an outlet tester replace a multimeter?', 'a' => 'For wiring checks it is faster and needs no skill to read. A multimeter gives actual voltage and more detail, so many technicians carry both.' ),
		),
		'hvac-guide' => array(
			array( 'id' => 'hv1', 'q' => 'What is the most important multimeter range for HVAC work?', 'a' => 'Capacitance. A weak run or start capacitor is behind many "motor hums but will not start" calls, and a meter with a uF range tests it in seconds. Voltage, continuity and temperature matter too.' ),
			array( 'id' => 'hv2', 'q' => 'Do I need true RMS for HVAC?', 'a' => 'Yes if you work on variable-speed drives and inverter compressors, where average-responding meters read low on non-sine waveforms. True RMS gives accurate readings on modern equipment.' ),
			array( 'id' => 'hv3', 'q' => 'What CAT rating do I need for HVAC work?', 'a' => 'CAT III 600 V is the working minimum, because HVAC service happens at the panel, the disconnect and the compressor, where transient energy is high.' ),
			array( 'id' => 'hv4', 'q' => 'Can one meter handle voltage, capacitance and temperature?', 'a' => 'Yes. A multimeter with a capacitance range and a temperature probe input covers the daily HVAC measurements in one tool. CHINYOO also makes separate temperature instruments if you prefer.' ),
		),
		'clamp-howto' => array(
			array( 'id' => 'ch1', 'q' => 'Why does my clamp meter read zero on a cable?', 'a' => 'You clamped around both conductors of a two-core cable, and the outgoing and return current canceled out. The jaws must surround only the single live conductor.' ),
			array( 'id' => 'ch2', 'q' => 'Can a clamp meter measure DC current?', 'a' => 'Only an AC+DC clamp meter with a Hall-effect sensor can. Standard AC-only models measure AC only. The VC3269 and VC3270 read both AC and DC.' ),
			array( 'id' => 'ch3', 'q' => 'What is inrush current and why does it matter?', 'a' => 'When a motor starts it draws a spike of current several times its running current for a fraction of a second. That spike trips breakers, and a clamp meter with inrush capture shows the peak.' ),
			array( 'id' => 'ch4', 'q' => 'Which is better, a clamp meter or a multimeter?', 'a' => 'For current on live motors and panels, the clamp meter is safer and faster. For voltage, resistance and fine troubleshooting, the multimeter is more precise. Most technicians carry both.' ),
		),
		'dt830-guide' => array(
			array( 'id' => 'd8301', 'q' => 'What is the DT830 digital multimeter used for?', 'a' => 'It measures DC/AC voltage, resistance, continuity and diode drops for household and light industrial checks. It is the budget meter that covers most basic troubleshooting.' ),
			array( 'id' => 'd8302', 'q' => 'What is the difference between the DT830B and DT830D?', 'a' => 'They share the same case and dial. The DT830D adds a continuity buzzer and transistor test socket, which is why distributors tend to prefer it as the default.' ),
			array( 'id' => 'd8303', 'q' => 'Is the DT830 accurate enough for professional work?', 'a' => 'For basic voltage, continuity and resistance checks, yes. It is not true RMS and has a low count, so for inverter circuits or precision measurements choose a higher-count model.' ),
			array( 'id' => 'd8304', 'q' => 'Can I buy the DT830 series in bulk with my own brand?', 'a' => 'Yes — CHINYOO is the factory. We supply the DT830 series for wholesale and OEM/ODM programs with custom branding, packaging and CE/RoHS documentation.' ),
		),
		'dt9205-guide' => array(
			array( 'id' => 'dt9205q1', 'q' => 'What can the DT9205A measure that a basic 830-class meter cannot?', 'a' => 'The DT9205A adds a capacitance range from 20 nF to 200 µF, a 20 A current path on both DC and AC, and a transistor hFE socket, on the same 1999-count display a basic meter uses. Those three ranges are exactly what separates it from the 830 class for electronics and bench repair.' ),
			array( 'id' => 'dt9205q2', 'q' => 'What is the accuracy of the DT9205A?', 'a' => 'DC voltage is rated ±1.5%, AC voltage ±1.8% and resistance ±2.0% on the useful ranges, with the 200 MΩ range at ±7.0%. Each unit passes the same calibration bench at our Wenzhou factory before packing.' ),
			array( 'id' => 'dt9205q3', 'q' => 'How do I measure a capacitor with the DT9205A safely?', 'a' => 'Discharge the capacitor first, ideally by bridging the terminals with a resistor rated for the stored voltage, then select the manual capacitance range close to the expected value and read. A capacitor that reads far under its printed value, or shows open, should be replaced.' ),
			array( 'id' => 'dt9205q4', 'q' => 'Can I buy the DT9205A in bulk or with my own brand?', 'a' => 'Yes — CHINYOO is the factory. We supply the DT9205A for wholesale and OEM/ODM programs with custom branding, packaging and CE/RoHS documentation. Send us your target quantity for a quotation.' ),
		),
		'vt-vs-mm' => array(
			array( 'id' => 'vtvm1', 'q' => 'Is a voltage tester better than a multimeter?', 'a' => 'Neither is better overall; they answer different questions. A voltage tester gives a fast yes or no for "is this live". A multimeter gives the actual voltage, current, resistance and continuity values. Keep the tester for quick safety checks and the meter for diagnosis.' ),
			array( 'id' => 'vtvm2', 'q' => 'Can a voltage tester replace a multimeter?', 'a' => 'For presence checks, yes. For measuring exact values, no. A tester tells you power is there; a meter tells you it reads 3.8 V when it should be 5 V, which is how most faults are found.' ),
			array( 'id' => 'vtvm3', 'q' => 'When should I use a non-contact voltage tester?', 'a' => 'Use it for the fast "is it live" check before touching a cable or outlet, for tracing where a wire stops carrying power, and for sorting the live wire from the neutral. It works without touching the conductor, so it is quick and safe at a distance.' ),
			array( 'id' => 'vtvm4', 'q' => 'Which should I buy first, a tester or a multimeter?', 'a' => 'If most of your work is safety checks and verifying lines are dead, start with a voltage tester. If you diagnose, measure or repair, start with a multimeter. Most trades end up carrying both. The VD06 pen and the DT700 meter cover the pair.' ),
		),
		'auto-mm-buy' => array(
			array( 'id' => 'amm1', 'q' => 'What is the best multimeter for automotive work?', 'a' => 'One that covers DC voltage, continuity and resistance at minimum, with a backlit display and auto-ranging. For modern engine work, add duty-cycle and RPM functions. For most DIY jobs, a compact meter like the DT700 series covers battery, fuse and alternator checks.' ),
			array( 'id' => 'amm2', 'q' => 'What voltage should a car battery show on a multimeter?', 'a' => 'Engine off, a healthy battery reads about 12.6 V resting. Below 12.4 V it is low, and below 12.0 V it is discharged. With the engine running, the alternator should charge between 13.5 V and 14.5 V.' ),
			array( 'id' => 'amm3', 'q' => 'Can I use a regular multimeter on a car instead of a test pen?', 'a' => 'Yes. A digital multimeter measures battery voltage, alternator output, fuse continuity and sensor resistance. A car test pen is faster for tracing power and ground in 12V/24V circuits. We supply both.' ),
			array( 'id' => 'amm4', 'q' => 'Do I need an automotive-specific multimeter?', 'a' => 'Not necessarily. A general digital multimeter with the right ranges works on a 12V vehicle. Automotive-specific models add duty-cycle, RPM and temperature functions for injection and ignition work. Match the functions to the jobs you actually do.' ),
		),
		'iat-1881' => array(
			array( 'id' => 'ind1', 'q' => 'We stock voltage testers for electricians and maintenance teams. Is the 1881 worth carrying as one compact tester that covers several jobs?', 'a' => 'Yes, the 1881 is a pen-style digital voltage tester with an LCD and a neon phase lamp that packs several detection modes behind a single test button. In direct detection it reads actual voltage across the 12V to 220V AC/DC range, and it also senses live wires without touching the metal and finds hidden wire breaks. For your customers, one compact pen covers jobs that would otherwise need several tools.' ),
			array( 'id' => 'ind2', 'q' => 'Our customers trace wiring faults and lose time on hidden breaks. Does the 1881 find a broken wire without opening the cable?', 'a' => 'Yes. In breakpoint detection mode the 1881 runs its tip along the live conductor, and the display stays lit while the wire is intact. At the point of a hidden break the signal stops, so your customer gets a confirmed location to cut and repair instead of opening a whole cable run. That is a real time-saver for electricians who work on existing installations.' ),
			array( 'id' => 'ind3', 'q' => 'Our safety checks require confirming a wire is dead before a tech works on it. Can the 1881 confirm that without touching the metal?', 'a' => 'Yes. In inductive sensing mode the 1881 reads the electric field around a conductor through the insulation, so it confirms a live wire without any contact with metal. That is useful for tracing cables behind insulation or checking that a circuit is truly dead before work starts. The pen-style tip and non-contact check let your crew stay at a safe distance.' ),
			array( 'id' => 'ind4', 'q' => 'Can I order samples, OEM, or bulk of the 1881?', 'a' => 'Yes. We support sample orders for evaluation, OEM and private label with your own brand on the 1881, and bulk orders at factory direct pricing. Lead time depends on the quantity and customization, and we confirm it when you send your inquiry. Tell us your target market and order size and our factory team will quote accordingly.' ),
		),
		'vc3268-guide' => array(
			array( 'id' => 'vc3268q1', 'q' => 'What can the VC3268 measure beyond AC clamp current?', 'a' => 'The VC3268 is a 4000-count clamp meter that also measures DC voltage, AC voltage, DC clamp current, resistance, frequency to 4 MHz and capacitance to 4 mF, with diode and continuity tests. It is a compact multimeter wrapped around the clamp, not a bare AC-only meter.' ),
			array( 'id' => 'vc3268q2', 'q' => 'Can the VC3268 measure DC current?', 'a' => 'Yes. The DCA mode reads DC current on the same 40 A and 400 A clamp ranges, which is what lets it cover solar inverter strings, battery circuits and DC-fed panels as well as AC motor loads.' ),
			array( 'id' => 'vc3268q3', 'q' => 'Should a distributor stock the VC3268, the 266 series or the VC3270?', 'a' => 'They cover three positions. The VC3268 is the compact 4000-count generalist for panel and light industrial work. The 266 series reaches 1000 A for the industrial end, and the VC3270 adds a large jaw for thick feeders and bus bars. Most distributors start with the VC3268 and add the others by channel.' ),
			array( 'id' => 'vc3268q4', 'q' => 'Can I buy the VC3268 in bulk or with my own brand?', 'a' => 'Yes — CHINYOO is the factory in Wenzhou. We supply the VC3268 for wholesale and OEM/ODM programs with custom branding, packaging and CE/RoHS documentation. Send us your target quantity for a quotation.' ),
		),
		'a830l-guide' => array(
			array( 'id' => 'a8301', 'q' => 'What can the A830L digital multimeter measure?', 'a' => 'DC voltage to 500 V at ±1.5%, AC voltage on 200 V and 500 V ranges at ±3.0%, DC current from 2 mA to 10 A, and resistance to 2 MΩ at ±2.0%, plus diode test, continuity buzzer and a dedicated 1.5 V battery test that the rest of the 830 family does not carry.' ),
			array( 'id' => 'a8302', 'q' => 'What makes the A830L different from other budget multimeters?', 'a' => 'It adds a 1.5 V battery test as a switch position, a backlight for low-light work and data hold to freeze a reading — features that are normally reserved for more expensive meters. On a 1999-count LCD those three extras are what set it apart on a shelf of budget meters.' ),
			array( 'id' => 'a8303', 'q' => 'Can the A830L test a 9 V battery?', 'a' => 'The dedicated battery position is built for 1.5 V cells (AA and AAA). For a 9 V smoke-alarm battery, switch to the DC voltage range and read the value directly. Rechargeables should be checked by chemistry first, since the test is designed for common alkaline cells.' ),
			array( 'id' => 'a8304', 'q' => 'Can I buy the A830L in bulk or with my own brand?', 'a' => 'Yes — CHINYOO is the factory in Wenzhou. We supply the A830L for wholesale and OEM/ODM programs with custom branding, packaging and CE/RoHS documentation, backed by the same 12-month warranty as the rest of the range. Send us your target quantity for a quotation.' ),
		),
		'88-2-guide' => array(
			array( 'id' => 'pen1', 'q' => 'What does the 88-2 test pen detect?', 'a' => 'It tells you whether a conductor is live and shows the voltage value on its digital display. Built to the Chinese inspection standard GB8218-2003 with SGS certification, the 88-2 covers 12 to 220 V AC with a 250 V maximum — the socket, switch and cable-run range for household and light commercial work — with a sound-and-light alert on a live wire.' ),
			array( 'id' => 'pen2', 'q' => 'How do I use the 88-2 correctly?', 'a' => 'Hold the pen by the insulated body, keep a finger on the metal cap at the back, and touch the tip to the conductor. Your body is the return path: within about two seconds the digital display shows the live voltage value and a sound-and-light alert confirms it. No range switching — touch, read, done.' ),
			array( 'id' => 'pen3', 'q' => 'Can the 88-2 replace a multimeter?', 'a' => 'No. The 88-2 reads voltage only — it shows the live value, but measures no continuity, resistance or current, and is not rated for high-voltage or three-phase industrial work. Pair it with a multimeter for full diagnosis.' ),
			array( 'id' => 'pen4', 'q' => 'Does the 88-2 carry certifications and can I order in bulk?', 'a' => 'Yes. Production follows GB8218-2003 with sampling per GB8218-87 S-4, the SGS certificate number is printed on the spec sheet, and the unit is CE and RoHS compliant with a 12-month warranty. CHINYOO is the factory, so wholesale and OEM branding are available.' ),
		),
		'dm6266-guide' => array(
			array( 'id' => 'dm6266q1', 'q' => 'What is the maximum current the DM6266F clamp meter measures?', 'a' => 'Up to 1000 A AC on the 200 A and 1000 A clamp ranges — enough for motor feeds, distribution panels and drive outputs, measured without breaking the circuit.' ),
			array( 'id' => 'dm6266q2', 'q' => 'Can the DM6266F measure frequency?', 'a' => 'Yes. It reads frequency up to 2000 Hz, which covers the output of most variable-frequency drives and lets you verify motor speed without a second instrument.' ),
			array( 'id' => 'dm6266q3', 'q' => 'How is the DM6266F different from the VC3268?', 'a' => 'The VC3268 is a compact 4000-count clamp with DC current and capacitance. The DM6266F is the simpler, sturdier workhorse: 1000 A AC, a 2000 MΩ resistance ceiling and a frequency range, on a 1999-count display.' ),
			array( 'id' => 'dm6266q4', 'q' => 'Can I buy the DM6266F in bulk or with my own brand?', 'a' => 'Yes — CHINYOO is the factory in Wenzhou. We supply the DM6266F for wholesale and OEM/ODM programs with custom branding, packaging and CE/RoHS documentation. Send us your target quantity for a quotation.' ),
		),
		'mvd765-guide' => array(
			array( 'id' => 'mvd765q1', 'q' => 'What does the MVD765 series test pen do?', 'a' => 'It reads live voltage from 12 V to 220 V AC with a 250 V maximum, showing the actual value on a digital display while a sound-and-light alert confirms the live wire. Built to GB8218-2003 with SGS certification.' ),
			array( 'id' => 'mvd765q2', 'q' => 'What is the difference between the MVD765A, B, C and D?', 'a' => 'They share the same electrical specification and certifications. The difference is packaging: an 8-piece retail box, a 20-piece display box and a blister card, so one pen family covers different sales channels.' ),
			array( 'id' => 'mvd765q3', 'q' => 'Can the MVD765 replace a multimeter?', 'a' => 'No. The MVD765 reads voltage only, with no continuity or resistance ranges, and stays in the 12-220 V household and light-commercial range. Pair it with a multimeter for full diagnosis.' ),
			array( 'id' => 'mvd765q4', 'q' => 'Can I buy the MVD765 series in bulk or with my own brand?', 'a' => 'Yes — CHINYOO is the factory. We supply the MVD765 series for wholesale and OEM/ODM programs with custom branding, packaging and CE/RoHS documentation, including the GB8218 and SGS paperwork.' ),
		),
		'temp-dmm-guide' => array(
			array( 'id' => 'td1', 'q' => 'How does a multimeter measure temperature?', 'a' => 'Through a K-type thermocouple probe that plugs into a dedicated jack marked with a thermometer symbol. Two different metals meet at the probe tip and generate a small voltage that changes with temperature; the meter reads that voltage and converts it to a reading on the display. Modern meters handle cold-junction compensation internally, so the reading is accurate at the tip, not just at the plug.' ),
			array( 'id' => 'td2', 'q' => 'What temperature range can a multimeter with a temperature probe measure?', 'a' => 'Most of our temperature-capable meters read from -20°C to 1000°C with accuracy of roughly ±2% to ±3%, which covers HVAC vents and evaporators, motor and bearing checks, appliance repair and water heaters. On most models the K-type probe is included in the box.' ),
			array( 'id' => 'td3', 'q' => 'Which CHINYOO multimeters include temperature measurement?', 'a' => 'Our temperature-capable line runs from the DT838 and DM102 entry models through the MY62, MY64 and VC17B, up to the VC9805 and VC9808 higher-count meters — 15 models in total. If you are buying for a team, tell us the temperatures you measure in a normal week and we will match the meter and probe style to the work.' ),
			array( 'id' => 'td4', 'q' => 'Can I buy a temperature-capable multimeter in bulk or with my own brand?', 'a' => 'Yes — CHINYOO is the factory in Wenzhou. We supply temperature-capable multimeters for wholesale and OEM/ODM programs with custom branding, packaging and CE/RoHS documentation. A sample goes out before any production order.' ),
		),
		'vc9805-guide' => array(
			array( 'id' => 'vc9805q1', 'q' => 'What makes the VC9805 different from a basic 1999-count multimeter?', 'a' => 'The VC9805 is a full-size bench meter with a 4000-count backlit display, non-contact voltage detection, capacitance to 400 µF, frequency to 4 MHz and a transistor hFE test. A basic 1999-count meter reads voltage, resistance and current; the VC9805 adds the functions a repair bench uses daily.' ),
			array( 'id' => 'vc9805q2', 'q' => 'Can the NCV function tell me whether a wire is live?', 'a' => 'Yes. The non-contact voltage function detects the induced electric field, so you can confirm a conductor or terminal is live without touching bare metal. Always verify with a contact voltage reading before you rely on the result.' ),
			array( 'id' => 'vc9805q3', 'q' => 'What are the safety and quality checks on the VC9805?', 'a' => 'Every VC9805 passes a calibration and function check at our Wenzhou factory before packing, is CE and RoHS compliant, carries overload protection on all ranges and is covered by the 12-month warranty with 24-hour engineer support.' ),
			array( 'id' => 'vc9805q4', 'q' => 'Can the VC9805 be sold under our own brand?', 'a' => 'Yes. We offer OEM branding and packaging for the VC9805. Order a sample first, tell us the quantity and destination, and our team will confirm lead time and per-unit pricing.' ),
		),
		'st105-guide' => array(
			array( 'id' => 'st105q1', 'q' => 'What is dwell angle and why does it need measuring?', 'a' => 'In a distributor with mechanical breaker points, dwell angle is how long the points stay closed, measured in degrees of distributor rotation. Too little dwell weakens the spark; too much wears the points. Setting dwell to the service-manual figure is part of a tune-up on points-and-condenser ignition.' ),
			array( 'id' => 'st105q2', 'q' => 'Which vehicles is the ST105 meant for?', 'a' => 'The ST105 is built for gasoline engines with distributor and breaker-point ignition: classic cars, older motorcycles and small-engine work, plus auto-electronics teaching. Its RPM and dwell scales are selected for 4-, 6- and 8-cylinder engines. Modern vehicles with electronic ignition do not expose these signals.' ),
			array( 'id' => 'st105q3', 'q' => 'Does the ST105 measure AC current?', 'a' => 'No. The current ranges are DC only, from 200 mA to 10 A. AC voltage is measured to 750 V, and the meter also reads resistance and capacitance, but there is no AC current range and no backlight.' ),
			array( 'id' => 'st105q4', 'q' => 'How do I check the RPM and dwell readings are right?', 'a' => 'Run the ST105 next to a known-good tachometer or dwell meter on the same engine and confirm the numbers track. The printed manual in the box shows the connection for each 4-, 6- or 8-cylinder setting.' ),
		),
		'bt168-guide' => array(
			array( 'id' => 'bt168q1', 'q' => 'Which batteries can the BT-168 test?', 'a' => 'The BT-168 checks 1.5 V AAA, AA, C and D cells and small button batteries, plus 9 V types such as 6F22, PP3 and MN1604. Its own scale prints the good, weak and replace thresholds for the 1.5 V and 9 V families.' ),
			array( 'id' => 'bt168q2', 'q' => 'Why can a battery read fine on a multimeter but fail here?', 'a' => 'A multimeter measures the cell at rest, with almost no load, so a worn cell can still show close to its rated voltage while collapsing under the current a device draws. The BT-168 loads the cell the way a device does, which is why it separates good cells from weak ones.' ),
			array( 'id' => 'bt168q3', 'q' => 'What do the good, weak and replace zones mean?', 'a' => 'For a 1.5 V cell: good is 1.28 to 1.5 V, weak is 1.15 to 1.27 V, and replace is below about 1.14 V. For a 9 V battery: good is 7.8 to 9 V, weak is 6 to 7.7 V, and replace is below about 5.9 V.' ),
			array( 'id' => 'bt168q4', 'q' => 'Does the BT-168 need its own battery?', 'a' => 'No. The tester is powered by the battery under test, so it cannot drain or need its own cell. It is not a charger or a capacity analyser; it gives a fast good, weak or replace verdict on the standard household sizes.' ),
		),
		'vc890-guide' => array(
			array( 'id' => 'vc890q1', 'q' => 'What is the difference between the VC890D and the VC890L?', 'a' => 'One thing: the VC890L has a backlit LCD and the VC890D does not. Everything else is identical — the 1999-count display, the same ranges and accuracy, the same housing, test leads, holster and 9 V battery. The two models share the same spare parts and the same carton.' ),
			array( 'id' => 'vc890q2', 'q' => 'What can the VC890 measure?', 'a' => 'DC and AC voltage from 200 mV to 1000 V and 750 V, DC and AC current from 2 mA to 10 A, resistance from 200 Ω to 200 MΩ, and capacitance from 20 nF to 200 µF, plus diode test, transistor hFE, a continuity buzzer, data hold and overload protection on every range.' ),
			array( 'id' => 'vc890q3', 'q' => 'How is the VC890 packed for export?', 'a' => 'Each meter ships in a colour box with the battery fitted, test leads, holster and manual. Forty pieces go into one carton measuring 43.5 × 30.5 × 51 cm, gross 16.4 kg and net 15.4 kg.' ),
			array( 'id' => 'vc890q4', 'q' => 'Can we order the VC890 under our own brand?', 'a' => 'Yes. CHINYOO is the factory in Wenzhou — we supply the VC890D and the VC890L for wholesale and OEM/ODM programs with custom branding, packaging and CE/RoHS documentation. A sample goes out before any production order.' ),
		),
	
    'dt33-guide' => array(
        array(
            'id' => 'dt331',
            'q' => 'What is the difference between the DT33B, DT33C and DT33D?',
            'a' => 'Only the third function. The DT33B adds a battery test for 1.5 V, 9 V and 12 V cells; the DT33C adds contact temperature from -20 °C to 1000 °C; the DT33D adds a square wave output. The housing, the 1999-count display and the DC and AC voltage ranges are identical across all three.',
        ),
        array(
            'id' => 'dt332',
            'q' => 'What can the DT33 series measure?',
            'a' => 'DC voltage from 200 mV to 500 V, AC voltage from 200 V to 500 V, DC current from 200 µA or 2000 µA up to 10 A, and resistance from 200 Ω to 20 MΩ or 200 MΩ, plus diode test, a continuity buzzer, data hold and a backlight. Every model is rated CAT I 600 V and CAT II 300 V.',
        ),
        array(
            'id' => 'dt333',
            'q' => 'Which DT33 model should a distributor stock?',
            'a' => 'Match the added function to your channel. Retail and hardware counters sell the DT33B, because a battery test is easy to demonstrate at the counter. HVAC and refrigeration distributors sell the DT33C for its temperature range. Electronics supply and education accounts buy the DT33D for signal tracing.',
        ),
        array(
            'id' => 'dt334',
            'q' => 'Can we order the DT33 series under our own brand?',
            'a' => 'Yes. CHINYOO is the factory in Wenzhou. We supply the DT33B, the DT33C and the DT33D for wholesale and OEM/ODM programs with custom branding, packaging and adjustable ranges. All three variants pack 60 pieces to a carton measuring 30.8 × 28.3 × 62 cm.',
        ),
    ),
		'cap-guide' => array(
			array( 'id' => 'capg1', 'q' => 'What is the difference between testing a capacitor with a multimeter and with an ESR meter?', 'a' => 'A multimeter measures capacitance, so it tells you whether the value has drifted away from the printed marking. An ESR meter measures equivalent series resistance, which is what actually fails first in electrolytics. A capacitor can read the correct capacitance and still have an ESR high enough to stop a circuit working, so the two instruments answer different questions.' ),
			array( 'id' => 'capg2', 'q' => 'Can a multimeter test a capacitor without removing it from the board?', 'a' => 'It will give you a reading, but not one you can act on. Everything connected in parallel with the capacitor adds to the measurement, and any discharge path makes the value drift while you watch it. Desoldering one leg is the only way to get a figure that reflects the capacitor alone.' ),
			array( 'id' => 'capg3', 'q' => 'How do I discharge a capacitor safely before testing it?', 'a' => 'Disconnect the power, then bridge the capacitor terminals with a resistor of a few kilohms rather than a screwdriver. A screwdriver draws an arc that can damage the capacitor and the board. Large electrolytics hold a dangerous charge for minutes after power is removed, so measure the voltage across them before you touch anything.' ),
			array( 'id' => 'capg4', 'q' => 'Which capacitor types can a multimeter test?', 'a' => 'Any capacitor within the instrument\'s capacitance range, which on our meters runs from 4 nF to 4 mF on the DT6222 and up to 40 mF on the XT802. Ceramic parts below a few nanofarads sit near the bottom of a handheld meter\'s resolution, and electrolytics above the top of the range need a dedicated instrument.' ),
		),
		'bvc-guide' => array(
			array( 'id' => 'bvcg1', 'q' => 'What voltage is 100% charged on a 12 V car battery?', 'a' => '12.65 V or above at rest. The scale below that runs 12.45 V for about 75%, 12.24 V for about 50%, 12.06 V for about 25%, and 11.89 V or below for a flat battery. Take the reading with the engine and all loads off, and let the battery stand before you measure.' ),
			array( 'id' => 'bvcg2', 'q' => 'Should I test a car battery with the engine running?', 'a' => 'Yes, because the three readings answer three different questions. Resting voltage gives the state of charge, the cranking voltage shows whether the battery can deliver starting current, and the reading with the engine running shows whether the alternator is charging — a healthy charging system holds roughly 13.5 V to 14.5 V at idle.' ),
			array( 'id' => 'bvcg3', 'q' => 'Is 12.2 V a bad car battery?', 'a' => 'It is a discharged one rather than necessarily a failed one. 12.24 V corresponds to roughly 50% state of charge, so the battery needs charging before you judge it. Charge it, let it rest, and measure again: if it returns to 12.65 V or above and holds, the battery is serviceable, and if it falls back quickly the battery or the charging system needs attention.' ),
			array( 'id' => 'bvcg4', 'q' => 'Does temperature affect the reading I get from a car battery?', 'a' => 'Yes. A cold battery reads slightly lower for the same state of charge and accepts charge more slowly, which is why a battery that is marginal in summer often fails on the first cold morning. Compare readings taken at similar temperatures, and treat a battery that measures below 12.45 V in cold weather as one to charge and retest rather than one to condemn.' ),
		),
		'fuse-guide' => array(
			array( 'id' => 'fuseg1', 'q' => 'What is the difference between testing a fuse with a multimeter and with a test light?', 'a' => 'A test light shows presence or absence of voltage and needs the circuit powered; a multimeter can measure both voltage and continuity, so it also works with the circuit isolated. For a blade fuse in a live fuse box the test light is faster, while for a fuse you have already pulled the multimeter\'s continuity mode is the more definite answer.' ),
			array( 'id' => 'fuseg2', 'q' => 'How do I test a fuse with a multimeter when the fuse is still in its holder?', 'a' => 'Set the meter to DC volts, connect the black probe to a known ground and touch the red probe to each of the two test points on top of the fuse in turn. A good fuse shows the same voltage at both points; a blown fuse shows supply voltage at one and nothing at the other. This works because the test points sit on either side of the element.' ),
			array( 'id' => 'fuseg3', 'q' => 'Why does a circuit breaker trip immediately after I reset it?', 'a' => 'A breaker that trips again straight away is reporting a fault in the circuit rather than failing itself. Disconnect the loads one at a time and reset between attempts to find which branch carries the fault, and check for a short between live and neutral or a live-to-earth fault. Repeatedly resetting into a live fault is how breakers and wiring get damaged.' ),
			array( 'id' => 'fuseg4', 'q' => 'Can I test a fuse or breaker while the circuit is live?', 'a' => 'You can measure voltage at a fuse with the circuit live, which is the normal way to test a blade fuse in place. Continuity and resistance tests must be done with the circuit isolated and the fuse removed, because the meter supplies its own test current and any parallel path in a live circuit makes the result meaningless. Never work on a breaker with the circuit energised.' ),
		),
		'ghost-guide' => array(
			array( 'id' => 'ghostg1', 'q' => 'Does a ghost voltage mean the wiring is faulty?', 'a' => 'No. Capacitive coupling between parallel conductors is normal in a working installation, and it is the instrument reporting a real field rather than a defect being present. Ghost voltage becomes a hazard only if someone reads a non-contact indication as proof that a conductor is live, or reads a silent tester as proof that one is dead.' ),
			array( 'id' => 'ghostg2', 'q' => 'Why does the same tester give different results for two different people?', 'a' => 'A non-contact tester needs a return path for the small current it senses, and that path runs through the operator\'s body to the surroundings. Gripping the body firmly couples better than touching it with a fingertip, while insulating gloves, insulated footwear and an insulated floor all reduce the path and make the instrument appear less sensitive. The tester is behaving consistently; the conditions are not.' ),
			array( 'id' => 'ghostg3', 'q' => 'Can a non-contact tester miss a live conductor?', 'a' => 'Yes, and that is the dangerous failure. Earthed metal conduit or an armoured screen puts a large capacitance between the live conductor and earth, so very little of the voltage appears where the tester can sense it; thick insulation has a similar effect. A weak battery reduces sensitivity gradually and without warning, so replace it before it degrades rather than after.' ),
			array( 'id' => 'ghostg4', 'q' => 'What is the correct sequence for proving a circuit is isolated?', 'a' => 'Prove the instrument on a known-live source, test the conductors you are about to work on, then prove the instrument again on the known-live source. The measurement that establishes isolation is a contact voltage measurement on the conductors themselves, made with a meter rated for the category and voltage of the location. A non-contact indication belongs at the front of the sequence, not at the end of it.' ),
		),
		'continuity-reading' => array(
			array( 'id' => 'contg1', 'q' => 'What resistance does a continuity beep actually mean?', 'a' => 'A beep means the resistance between the probes is below a threshold built into the meter, not that it is zero. On the XT802 the continuity range is printed on the panel as 0.1 to 50 ohms, so a joint measuring 30 ohms will beep on that instrument. A meter with a 10 ohm threshold stays silent on the same joint. Neither is wrong; they are answering slightly different questions.' ),
			array( 'id' => 'contg2', 'q' => 'Why does my meter beep with the probes apart?', 'a' => 'Either the probes are touching something you have not noticed, or the test leads themselves are intermittent. Touch the two probe tips together with nothing else in the circuit and read the value: a good pair comes in under 0.2 ohms. Anything higher, or a reading that jumps, means you are measuring the leads rather than the joint and the leads need replacing before any reading can be trusted.' ),
			array( 'id' => 'contg3', 'q' => 'Should I use continuity mode or the resistance range?', 'a' => 'Continuity mode is for speed over long cable runs, where the only question is whether a conductor is continuous end to end. When the actual resistance matters, change to the resistance range: on the DT6222 that covers 400 ohms to 40 megohms at plus or minus 1.2 percent, which is enough resolution to watch a contact degrade from 0.05 ohms to 0.5 ohms across successive services.' ),
			array( 'id' => 'contg4', 'q' => 'Why does a shorted diode beep on both continuity and diode test?', 'a' => 'Because a shorted junction conducts in both directions, so continuity sees a closed path and the diode test sees a forward drop near zero. The diode test is the function that separates them: a healthy silicon junction reads roughly 0.3 to 0.7 volts forward and open circuit in reverse, and a part that reads near zero both ways has failed short. Use the diode position whenever the circuit contains a semiconductor.' ),
		),
		'current-jack' => array(
			array( 'id' => 'jackg1', 'q' => 'Why does the same current read differently on the 10 A jack and the mA jack?', 'a' => 'Because the two jacks connect the meter to two different shunts. The 10 A jack uses a heavy, low-resistance shunt that can carry large current; the mA jack uses a lighter, higher-resistance one that gives finer resolution. The reading comes from whichever jack the red lead is in, so a lead left in the 10 A jack while measuring a few milliamps produces a number dominated by that range\'s resolution floor.' ),
			array( 'id' => 'jackg2', 'q' => 'What does the 10 second maximum on the 10 A jack mean?', 'a' => 'It is a duty limit, not a formality. Ten amps through the shunt produces heat, and inside a hand-held case that heat has nowhere to go, so the jack is rated for 10 seconds and then needs to cool. A meter left connected on a 10 amp load will drift as the shunt warms and the reading will walk away from the truth before anything visible happens to the instrument.' ),
			array( 'id' => 'jackg3', 'q' => 'How small a current can each layout measure?', 'a' => 'On the XT802 the single 10 A jack is marked 20 milliamps to 10 amps, so below 20 milliamps the display stops being meaningful — a 35 milliamp standby current is measurable there, a 4 milliamp one is not. The DT6222 puts microamps and milliamps on their own jack fused at 400 milliamps, with 400 microamp, 4000 microamp, 40 milliamp and 200 milliamp ranges at plus or minus 1.4 percent on a 4000-count display.' ),
			array( 'id' => 'jackg4', 'q' => 'Can I measure current without breaking the circuit?', 'a' => 'Not with a multimeter, because a current measurement works by putting the meter into the gap in the circuit. Above the meter\'s top current range the right instrument is a clamp meter, which goes around the conductor and never breaks it. Opening a 100 amp circuit to insert a meter is a hazard rather than a measurement, and a clamp can also stay connected indefinitely where a 10 A jack cannot.' ),
		),
		'parasitic-draw' => array(
			array( 'id' => 'pdg1', 'q' => 'How many milliamps of parasitic draw is normal?', 'a' => 'Most vehicles settle between 20 and 50 milliamps once every module has gone to sleep. Under 20 milliamps is normal and on the low side, 50 to 80 milliamps is borderline and worth re-measuring after a longer sleep period, and above 80 milliamps will flatten a healthy battery in days to weeks. Above 1 amp a module is awake or a lamp is staying on.' ),
			array( 'id' => 'pdg2', 'q' => 'How long do I have to wait before the reading means anything?', 'a' => 'Until the vehicle is fully asleep, which is anything from twenty minutes to an hour depending on the model. A car that has just been unlocked draws several amps, and reading during that window is the single most common reason a healthy car is diagnosed as faulty. Set the meter up, close everything, and leave it for the full sleep period the manufacturer specifies before reading anything at all.' ),
			array( 'id' => 'pdg3', 'q' => 'Which current range should I use for a parasitic draw test?', 'a' => 'The 40 milliamp range on a meter that has one. Forty milliamps sits near the top of that range, so a normal reading uses most of the scale and is easy to trust, and the last digit is 10 microamps — which resolves the difference between 20 and 25 milliamps. On the 10 A jack the same measurement returns a plausible-looking number that hides everything in the band you are trying to read.' ),
			array( 'id' => 'pdg4', 'q' => 'What if the draw comes and goes instead of staying steady?', 'a' => 'Then the intermittent behaviour is the finding rather than the number. Intermittent drains are usually a module that wakes on a timer, a boot or glovebox lamp on a tired switch, or a relay that is not releasing cleanly. Leaving the meter connected and watching the display over a longer period is the only way to catch one, so switch auto power off off before you start.' ),
		),
		'price-anatomy' => array(
			array( 'id' => 'priceg1', 'q' => 'Why can two multimeters that look identical differ in price by a factor of ten?', 'a' => 'Because the difference sits in parts that cannot be seen from outside the case. The fuse on the current jack, the protection network behind the jacks, the type of measurement chip, the tolerance class of the reference resistors and whether a per-unit calibration record is kept are all cost items, and none of them is visible on a shelf. Price also includes the channel: export freight, duty and two layers of margin sit between the factory gate and the retail shelf.' ),
			array( 'id' => 'priceg2', 'q' => 'Which line item should I ask about first when a quote looks too cheap?', 'a' => 'The fuse on the current jack. A high-breaking-capacity ceramic fuse rated for the full range is a genuine cost per unit, and replacing it with a glass fuse or leaving one jack without a fuse is the easiest way to move a bill of materials down. The saving is small; the consequence is that one mis-set range on a live circuit turns a measurement into an arc inside the case.' ),
			array( 'id' => 'priceg3', 'q' => 'Does a higher order quantity really lower the unit price?', 'a' => 'Yes, for two separate reasons. The first is ordinary purchasing leverage on components. The second is tooling: an enclosure, a silk screen, a colour match or a custom label carries a one-time cost that has to be spread across the run. At low volume that one-time cost dominates the unit price; at a few thousand units it disappears into the price of the parts.' ),
			array( 'id' => 'priceg4', 'q' => 'Can two meters at very different prices contain the same board?', 'a' => 'Yes. A small number of metering front ends serve a very large share of the world\'s instruments, so two meters far apart in price can genuinely share a converter. What differs is what surrounds it: the protection network, the reference parts, the enclosure, the display and the calibration regime. That is why comparing chips is not a useful way to compare meters.' ),
		),
		'gfi-gfci' => array(
			array( 'id' => 'gfig1', 'q' => 'Is a GFI the same thing as a GFCI?', 'a' => 'Yes. GFI and GFCI name the same device; the shorter form is simply more common in some regions and on some product labels. Both compare the current leaving on the live conductor with the current returning on the neutral, and both interrupt the circuit when that difference exceeds the threshold.' ),
			array( 'id' => 'gfig2', 'q' => 'At what current does a GFCI trip?', 'a' => 'Under UL 943 a Class A device trips at 5 milliamps plus or minus 1 milliamp, and it has to do so within a defined time. The threshold is set that low because the hazard being addressed is current through a person, not current through a conductor.' ),
			array( 'id' => 'gfig3', 'q' => 'Does a plug-in socket tester prove that a GFCI works?', 'a' => 'No. A tester with a GFCI test button proves that the device trips when a fault is simulated; it does not measure the current at which it tripped or the time it took. A device that trips at 8 milliamps and one that trips at 5 will both pass that check. Confirming the threshold needs an instrument that measures the trip current.' ),
			array( 'id' => 'gfig4', 'q' => 'Where is each term used?', 'a' => 'GFCI is the term used in North American codes and on North American product labels, and GFI appears on a large share of the devices sold through retail channels there. In the UK and Europe the same protective function appears under residual current device terminology, so a distributor matching a catalogue to a market should read the standard named on the device rather than the abbreviation on the box.' ),
		),
		'electrician-spec' => array(
			array( 'id' => 'elecg1', 'q' => 'Which specification should an electrician check first on a multimeter?', 'a' => 'The continuity threshold. Continuity checking is the most frequent measurement made on site, and the beep threshold differs between models: a meter that beeps up to 50 ohms will pass a joint that a meter with a 10 ohm threshold rejects. Two technicians with two meters can disagree about the same conductor and both be reading their own instrument correctly.' ),
			array( 'id' => 'elecg2', 'q' => 'Why does the current jack matter more than the current range?', 'a' => 'Because current is measured in series, so every milliamp passes through the instrument, and the jack determines which shunt the reading comes from. A single 10 A jack measures from 20 milliamps upward; a meter that splits microamps and milliamps onto a separate fused jack can measure a standby current that the 10 A input cannot see at all.' ),
			array( 'id' => 'elecg3', 'q' => 'How can I tell a good meter from a bad one before buying it?', 'a' => 'Short the two probes together and read the result. A good pair reads under 0.2 ohms, and that figure is the floor of everything the instrument will measure afterwards. A meter whose own leads measure one ohm cannot separate a good joint from a marginal one, because both sit inside its own error.' ),
			array( 'id' => 'elecg4', 'q' => 'Is a non-contact voltage detector enough to confirm that a circuit is dead?', 'a' => 'No. It responds to the electric field around a conductor, so it can be triggered by a neighbouring live cable, by capacitive coupling through a wall, or by a conductor connected to nothing at all. Treat it as a first pass that tells you where to put the probes; the confirmation has to come from a contact measurement on a known-good source followed by the conductor itself.' ),
		),
);
}

/** 新闻 FAQ 当前语言 dict(仅非 EN)。 */
function ew_news_faq_dict() {
	$lang = function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';
	if ( 'en' === $lang ) {
		return array();
	}
	static $dicts = null;
	if ( null === $dicts ) {
		$dicts = array( 'zh' => array(), 'es' => array(), 'ru' => array(), 'ar' => array() );
		foreach ( array_keys( $dicts ) as $l ) {
			$file = __DIR__ . '/news-faq-strings-' . $l . '.php';
			if ( file_exists( $file ) ) {
				$fn = 'ew_news_faq_dict_' . $l;
				require_once $file;
				if ( function_exists( $fn ) ) {
					$dicts[ $l ] = $fn();
				}
			}
		}
	}
	return isset( $dicts[ $lang ] ) ? $dicts[ $lang ] : array();
}

/** 当前语言某主题的问答列表。 */
function ew_news_faq( $topic_key ) {
	$all  = ew_news_faq_all();
	$dict = ew_news_faq_dict();
	$list = isset( $all[ $topic_key ] ) ? $all[ $topic_key ] : array();
	$out  = array();
	foreach ( $list as $qa ) {
		$q = $qa['q'];
		$a = $qa['a'];
		if ( isset( $dict[ $qa['id'] . 'q' ] ) && '' !== $dict[ $qa['id'] . 'q' ] ) {
			$q = $dict[ $qa['id'] . 'q' ];
		}
		if ( isset( $dict[ $qa['id'] . 'a' ] ) && '' !== $dict[ $qa['id'] . 'a' ] ) {
			$a = $dict[ $qa['id'] . 'a' ];
		}
		$out[] = array( 'q' => $q, 'a' => $a );
	}
	return $out;
}

/** 文章 → FAQ 主题键(按 EN 文章 slug)。未命中返回 ''。 */
function ew_news_faq_for_post( $post_id ) {
	$en_id = function_exists( 'pll_get_post' ) ? pll_get_post( (int) $post_id, 'en' ) : 0;
	$en_id = $en_id ? $en_id : (int) $post_id;
	$slug  = get_post_field( 'post_name', $en_id );
	$map   = ew_news_faq_topics();
	return isset( $map[ $slug ] ) ? $map[ $slug ] : '';
}

/* =====================================================================
 * FAQ 关键字自动加粗(渲染层)
 * 需求:FAQ 里核心词加粗。可见问答区块原本用 esc_html 输出 → 这里先 esc_html
 * 再包 <strong>,保持原转义不变。JSON-LD 直接取源数组纯文本,不受影响。
 * 核心词 = 品类词 + 关键规格词 + 商务词,不含型号名(避免整段花掉)。
 * ===================================================================== */

/** 当前语言。 */
function ew_faq_lang() {
	return function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';
}

/**
 * 当前语言核心词表(长词优先排序)。
 */
function ew_faq_keyword_list() {
	static $list = null;
	if ( null !== $list ) {
		return $list;
	}
	$map = array(
		'en' => array(
			// 品类
			'non-contact voltage tester', 'voltage tester', 'voltage testers',
			'digital multimeter', 'multimeter', 'multimeters',
			'clamp meter', 'clamp meters', 'battery tester', 'battery testers',
			'socket tester', 'socket testers', 'car electric pen', 'car electric pens', 'car test pen', 'car test pens',
			'infrared thermometer', 'thermometer', 'hygrometer',
			// 关键规格
			'true RMS', 'true-RMS', 'non-contact', 'auto-ranging', 'continuity',
			'capacitance', 'CAT rating', 'CAT II', 'CAT III', 'CAT IV',
			'6000-count', '2000-count', '12V', '24V',
			// 商务
			'OEM', 'ODM', 'MOQ', 'sample', 'samples', 'bulk', 'wholesale',
			'warranty', 'lead time', 'FOB', 'CE', 'RoHS', 'ISO 9001',
		),
		'zh' => array(
			'电压测试笔', '验电笔', '数字万用表', '万用表', '数字钳形表', '钳形表',
			'电池检测仪', '插座测试仪', '汽车测电笔', '测电笔', '感应式报警测试仪',
			'红外测温仪', '温度计', '温湿度计',
			'非接触式', '非接触', '真有效值', 'true-RMS', '自动量程', '通断', '电容', '频率',
			'安全等级', '起订量', '样品', '批量', '批发', '整柜',
			'质保', '保修', '交期', '货期', '交货日期',
			'OEM', 'ODM', 'MOQ', 'CE', 'RoHS', 'ISO 9001', 'FOB', '12V', '24V',
		),
		'es' => array(
			'comprobador de tensión', 'multímetro', 'pinza amperimétrica',
			'comprobador de baterías', 'comprobador de tomas de corriente',
			'lápiz comprobador', 'detector de tensión sin contacto', 'sin contacto',
			'termómetro infrarrojo', 'termómetro', 'higrómetro',
			'true RMS', 'true-RMS', 'muestras', 'muestra', 'garantía',
			'OEM', 'ODM', 'MOQ', 'CE', 'RoHS', 'ISO 9001', 'FOB', '12V', '24V',
		),
		'ru' => array(
			'Тестер напряжения', 'Мультиметр', 'Токоизмерительные клещи',
			'Тестер батареек', 'Тестер розеток', 'тестер-пробник',
			'бесконтактный', 'термометр', 'гигрометр',
			'true RMS', 'true-RMS', 'образцы', 'образец', 'гарантия',
			'OEM', 'ODM', 'MOQ', 'CE', 'RoHS', 'ISO 9001', 'FOB', '12V', '24V',
		),
		'ar' => array(
			'جهاز اختبار الجهد', 'ملتيميتر', 'جهاز القياس المشبكي',
			'جهاز فحص البطاريات', 'جهاز اختبار المقابس', 'قلم اختبار السيارات',
			'كاشف جهد بدون تلامس', 'بدون تلامس',
			'ميزان الحرارة', 'مقياس الرطوبة',
			'true-RMS', 'true RMS', 'عينات', 'ضمان',
			'OEM', 'ODM', 'MOQ', 'CE', 'RoHS', 'ISO 9001', 'FOB', '12V', '24V',
		),
	);
	$lang = ew_faq_lang();
	$list = isset( $map[ $lang ] ) ? $map[ $lang ] : $map['en'];
	// 长词优先,避免短词先命中把长词切碎。前缀冲突必在同文种内,字节长排序等价于字符长排序。
	usort( $list, function ( $a, $b ) {
		return strlen( $b ) - strlen( $a );
	} );
	return $list;
}

/**
 * 把文本里出现的关键字包 <strong>。先 esc_html 保证与原来一致的转义。
 */
function ew_faq_emph( $text ) {
	if ( '' === $text ) {
		return esc_html( $text );
	}
	$kws = ew_faq_keyword_list();
	if ( empty( $kws ) ) {
		return esc_html( $text );
	}
	$lang = ew_faq_lang();
	$alts = array();
	foreach ( $kws as $kw ) {
		$e = preg_quote( $kw, '/' );
		if ( 'ru' === $lang ) {
			// 俄语名词变格:第一个词允许词尾(Тестер / Тестера / Тестером)
			$w = explode( ' ', $e );
			$w[0] .= '\p{L}*';
			$e = implode( ' ', $w );
		}
		// 含拉丁字母/数字的词用拉丁边界;纯 CJK/阿语/西里尔词组用子串(无空格分词或词形固定)。
		// 边界只用拉丁字母/数字:否则"通过CE认证""وRoHS"里内嵌的拉丁词会被相邻汉字/阿语字母误挡。
		if ( preg_match( '/[A-Za-z0-9]/', $kw ) ) {
			$alts[] = '(?<![A-Za-z0-9])' . $e . '(?![A-Za-z0-9])';
		} else {
			$alts[] = $e;
		}
	}
	$pattern = '/' . implode( '|', $alts ) . '/iu';
	return preg_replace_callback(
		$pattern,
		function ( $m ) {
			return '<strong>' . $m[0] . '</strong>';
		},
		esc_html( $text )
	);
}