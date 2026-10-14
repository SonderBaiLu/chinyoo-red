<?php
/**
 * Scenario content — 场景页深度内容(What to measure / How to / Safety tips / Recommended instruments)
 *
 * 与 inc/scenarios.php 的场景定义配套:scenarios.php 提供 slug→标题/关键词/产品关联,
 * 本文件提供每个场景页正文的 4 个内容块,供 page-scenario.php 渲染。
 * 内容面向 B2B 买家与电工/维护技师,含具体测量方法与安全提示(SEO 长尾词载体)。
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * 返回指定场景的 4 个内容块;未知 slug 返回空数组。
 * 块结构:
 *   'what'        => heading / intro / items[]   —— 该场景测什么
 *   'how'         => heading / intro / steps[]   —— 怎么测(有序步骤)
 *   'tips'        => heading / intro / items[]   —— 安全与实操提示
 *   'recommended' => heading / intro / items[]   —— 适用仪表(name + why)
 */
function ew_scenario_content( $slug ) {
    $content = array(
        'electrical-maintenance' => array(
            'what' => array(
                'heading' => 'What to Measure in Electrical Maintenance',
                'intro'   => 'Electrical maintenance work centres on proving whether a circuit is live, continuous and correctly wired before anyone touches it. These are the checks a technician runs most often on a maintenance call.',
                'items'   => array(
                    'check continuity on a dead 230V outlet before calling it failed',
                    'trace a 12V car circuit that drops voltage under load',
                    'verify line and neutral polarity at a socket outlet before energising',
                    'confirm the absence of voltage before opening a breaker panel',
                    'measure live-to-earth voltage to spot a high-leakage neutral',
                    'check for a lost neutral by comparing hot to neutral and hot to earth',
                    'test a tripped breaker to see whether the fault sits in the load',
                ),
            ),
            'how' => array(
                'heading' => 'How to Check Voltage and Continuity',
                'intro'   => 'A multimeter with a continuity buzzer, a voltage tester and a clamp meter cover nearly every maintenance check. Follow the same order each time so nothing gets missed.',
                'steps'   => array(
                    'Test the meter first: set it to AC voltage mode and confirm it reads the expected supply value on a known-good live source before you trust any reading.',
                    'Prove the circuit dead: with the supply isolated, use AC voltage mode to check line to neutral, line to earth and neutral to earth until every reading is zero.',
                    'Run continuity checks: switch to continuity or buzzer mode, short the leads to confirm the tone, then test the conductor end to end and compare the reading with the circuit length.',
                    'Chase live loads: set the meter to AC voltage mode and measure across a suspect component while the circuit carries load, watching for an abnormal voltage drop under load.',
                    'Read the actual load current: close the clamp meter jaws around a single conductor, never around two phases, and read the AC amperage without breaking the circuit.',
                    'Confirm the install: plug a socket tester into the outlet to check wiring polarity and earth, then record the values before you close the job.',
                ),
            ),
            'tips' => array(
                'heading' => 'Safety First',
                'intro'   => 'Voltage checks are safe when the right habits are automatic. These rules apply whether you work on a factory floor or a domestic board.',
                'items'   => array(
                    'Check the meter CAT rating and inspect the leads for cracked insulation before any live work.',
                    'De-energise and prove dead with your own meter before touching conductors, even on a planned job.',
                    'At 230V use insulated gloves and one hand in your pocket to keep current from crossing your chest.',
                    'Hold the probes by the insulated grips and never touch the metal tips while they are connected.',
                    'Lock out the breaker and tag the panel when you leave the circuit isolated for someone else.',
                    'Test the meter on a known live source again after the job, so a meter that failed mid-check can never be missed.',
                ),
            ),
            'recommended' => array(
                'heading' => 'Recommended Instruments',
                'intro'   => 'For day-to-day electrical maintenance these instruments cover the checks above without over-reaching.',
                'items'   => array(
                    array(
                        'name' => 'M100 / M200 Pen-Type Voltage Testers',
                        'why'  => 'Gives a fast live or dead confirmation without lead fiddling before you pick up a multimeter.',
                    ),
                    array(
                        'name' => 'VC3270 / VC3269 Digital Clamp Meters',
                        'why'  => 'Measure load current with the jaws on a single conductor, so you can trace live circuits without breaking them.',
                    ),
                    array(
                        'name' => 'Socket Tester Outlet Receptacle Tester',
                        'why'  => 'Confirms wiring polarity and earth integrity at the outlet in one push, ideal for final socket checks.',
                    ),
                    array(
                        'name' => 'DT6221 Digital Meter',
                        'why'  => 'Handles AC voltage, DC voltage and continuity checks, so it is the everyday workhorse for maintenance.',
                    ),
                ),
            ),
        ),

        'automotive-testing' => array(
            'what' => array(
                'heading' => 'What to Measure on a Vehicle',
                'intro'   => 'Most vehicle electrical faults come down to wiring, battery or charging issues rather than a failed part. Measure the circuit first so you replace the right thing the first time.',
                'items'   => array(
                    'check continuity on a dead 12V circuit before tracing the fault',
                    'trace a car circuit that drops voltage under load when lights are on',
                    'measure battery resting voltage to spot a weak or undercharged cell',
                    'test alternator output with the engine idling and again under rev',
                    'verify fuse and relay continuity after a component stops working',
                    'check voltage drop across a corroded earth strap or battery terminal',
                    'confirm which pin stays live on a 12V or 24V wiring harness',
                ),
            ),
            'how' => array(
                'heading' => 'How to Trace a 12V or 24V Circuit',
                'intro'   => 'Work from the battery outward and always compare each live reading against a known-good reference. A steady sequence gives you readings you can trust and repeat.',
                'steps'   => array(
                    'Test the meter first: confirm the DC voltage reading on a known-good battery, then short the leads in continuity mode to hear the buzzer before working on the vehicle.',
                    'Set the meter to DC voltage mode and read the battery at the terminals with the engine off. A healthy 12V battery rests near 12.6V and a 24V system near 25.2V.',
                    'Switch to DC voltage mode and probe from ground to each point along the circuit with the load switched on. A drop above about 0.5V between battery and load points to a corroded terminal or thin wire.',
                    'Isolate the circuit, then use continuity or buzzer mode to check fuses, relays and wire runs. The meter should beep cleanly with no flicker when the part is sound.',
                    'Test the charging system: with the engine running, read battery voltage at the terminals at idle and again at a higher rpm — a healthy alternator lifts the reading into its working range and holds it steady.',
                ),
            ),
            'tips' => array(
                'heading' => 'Safety First on Live Circuits',
                'intro'   => '12V and 24V systems can still push serious current, so treat every wire as live until you prove otherwise.',
                'items'   => array(
                    'Treat every 12V or 24V wire as live until you prove otherwise — vehicle circuits can push serious current.',
                    'Disconnect the battery or pull the fuse where possible so you never probe a live circuit.',
                    'Check around the battery for acid or corrosion, and keep metal tools away from both terminals.',
                    'Never touch the bare probe tips while the leads are connected to a live circuit.',
                    'Inspect the leads for cracks or exposed copper before any live test.',
                    'Keep the work area dry and your feet clear of water.',
                ),
            ),
            'recommended' => array(
                'heading' => 'Recommended Instruments',
                'intro'   => 'Pick the tool that matches the checks you do most often on the vehicles you service.',
                'items'   => array(
                    array(
                        'name' => 'Wilk 302 / 303 Car Electric Pens',
                        'why'  => 'Track live and dead wires quickly on 12V and 24V car circuits.',
                    ),
                    array(
                        'name' => 'HW170 / HW171 / HW172 Battery Meters',
                        'why'  => 'Give a fast battery condition check without needing to disconnect the battery first.',
                    ),
                    array(
                        'name' => 'HW304–HW310 Series',
                        'why'  => 'Handle the routine voltage and continuity checks needed to trace a circuit fault.',
                    ),
                    array(
                        'name' => 'HW65 / HW68 / HW80 Series',
                        'why'  => 'Reliable everyday measurement for the wiring checks most workshop calls start with.',
                    ),
                ),
            ),
        ),

        'home-diy' => array(
            'what' => array(
                'heading' => 'What to Measure in Home Electrical Checks',
                'intro'   => 'Most household faults hide in the sockets, the wiring, the batteries and the temperature-sensitive appliances. Here is what a technician typically checks on a DIY or home call-out.',
                'items'   => array(
                    'Verify correct live-neutral-earthing polarity at every household socket',
                    'Check continuity on a dead 230V outlet before you open the wall',
                    'Measure battery voltage on rechargeable cells before deciding to charge',
                    'Trace a 12V car circuit that drops voltage when the load switches on',
                    'Confirm a battery charger delivers its rated voltage under a connected load',
                    'Confirm a heating element or appliance draws current only when it should',
                    'Record indoor and fridge temperatures to catch a failing thermostat',
                ),
            ),
            'how' => array(
                'heading' => 'How to Check Voltage, Continuity and Charge',
                'intro'   => 'A reliable routine tests the meter first, then works from the socket to the battery to the live circuit.',
                'steps'   => array(
                    'Test the meter before any live work: select AC voltage mode, confirm it reads the local supply on a known-good outlet, then touch the probes together to verify the continuity buzzer sounds.',
                    'Plug the Socket Tester Outlet Receptacle Tester into the suspect outlet and read the lamp pattern, then confirm any fault in AC voltage mode between live, neutral and earth.',
                    'Set the BT-168 Series for the cell type and read the load-tested verdict, or switch to DC voltage mode and compare open-circuit voltage with the rating stamped on the battery.',
                    'To chase a 12V circuit that sags, stay in DC voltage mode, energise the load, and watch for the voltage drop that betrays a corroded terminal or undersized cable.',
                    'For temperature, set the HTC-1 nearby and let the sensor settle for a few minutes before comparing its reading with the thermostat set point, then confirm a live appliance receives supply by checking AC voltage at its inlet.',
                ),
            ),
            'tips' => array(
                'heading' => 'Safety First: Household Electrical Work',
                'intro'   => 'A home circuit can still deliver a lethal shock, so treat every wire as live until you have proven it dead.',
                'items'   => array(
                    'De-energise the circuit at the breaker and lock it off wherever the job allows.',
                    'Pull the socket tester out before changing a fuse, and re-check polarity after any repair.',
                    'At 230V, wear insulated gloves and keep one hand away from the circuit.',
                    'Hold the probes only behind the finger guards and never touch the metal tips.',
                    'Check the meter CAT rating and inspect the leads for cracks before any live work.',
                    'Never work on wet surfaces, and keep hands and tools dry around the board.',
                ),
            ),
            'recommended' => array(
                'heading' => 'Recommended Instruments',
                'intro'   => 'These tools cover the everyday checks above with the least fuss and the lowest risk on a household call-out.',
                'items'   => array(
                    array(
                        'name' => 'Socket Tester Outlet Receptacle Tester',
                        'why'  => 'Instantly shows correct wiring, reversed live and lost earth on any household outlet.',
                    ),
                    array(
                        'name' => 'BT-168 Series',
                        'why'  => 'Gives a clear load-tested pass-fail verdict on AA, AAA and rechargeable batteries in seconds.',
                    ),
                    array(
                        'name' => 'HW171 / HW172 Battery Meters',
                        'why'  => 'Read exact battery voltage so you can tell a tired cell from a flat one.',
                    ),
                    array(
                        'name' => 'HTC-1',
                        'why'  => 'Tracks indoor temperature and humidity to flag a drifting thermostat or a damp room.',
                    ),
                ),
            ),
        ),

        'hvac-temperature' => array(
            'what' => array(
                'heading' => 'What to Measure in HVAC and Temperature',
                'intro'   => 'This scenario covers the temperature, voltage and current checks you run when servicing refrigeration, heating and air conditioning equipment.',
                'items'   => array(
                    'Measure ambient room temperature against the thermostat set point to spot drift',
                    'Trace a heating element that cycles off before the room reaches set point',
                    'Check supply and return duct temperatures to see how much heat the air loses',
                    'Verify refrigerant pipe surface temperatures when the compressor runs weak',
                    'Confirm the water heater reaches its target temperature without overshooting',
                    'Read the outside air temperature and compare it with the system design load',
                    'Monitor fridge and freezer temperature over time to catch slow creep',
                ),
            ),
            'how' => array(
                'heading' => 'How to Measure Temperature and Voltage',
                'intro'   => 'A consistent order of checks keeps the job safe and the readings meaningful.',
                'steps'   => array(
                    'Test the meter first: touch the probe tips together in continuity mode and confirm the buzzer sounds, then verify AC voltage mode against a known live outlet.',
                    'For ambient or surface temperature, connect the thermocouple, select temperature mode, and hold the sensor still until the reading settles.',
                    'To test a heating element, switch to resistance mode, de-energise the circuit, and measure across the element, expecting a low continuous resistance rather than an open circuit.',
                    'For ductwork, probe the insulated supply and return ducts a short distance from the unit and compare the two readings to judge delivered heat.',
                    'To check running current on a compressor or fan motor, clamp the jaws around a single conductor in AC current mode, never around a cable carrying both live and neutral.',
                ),
            ),
            'tips' => array(
                'heading' => 'Safety First',
                'intro'   => 'Follow these habits on every job, especially where mains voltage is involved.',
                'items'   => array(
                    'Check the meter CAT rating and the condition of its leads before any live work.',
                    'De-energise and lock out the circuit wherever possible, then verify absence of voltage before touching anything.',
                    'Clamp around a single conductor only — never around a cable carrying both live and neutral.',
                    'At 230V keep one hand in your pocket and wear insulated gloves with insulated probes.',
                    'Let the sensor settle before recording a temperature; a moving probe reads air, not the surface.',
                    'Replace any lead with cracked or damaged insulation before you use it again.',
                ),
            ),
            'recommended' => array(
                'heading' => 'Recommended Instruments',
                'intro'   => 'Pick from these instruments to cover the temperature, voltage and current checks in this scenario.',
                'items'   => array(
                    array(
                        'name' => 'XT300 Temperature Meters',
                        'why'  => 'Cover ambient, surface and duct temperature checks in one handheld meter.',
                    ),
                    array(
                        'name' => 'VC3270 Digital Clamp Meter',
                        'why'  => 'Measures running current on compressor and fan motors without breaking the circuit.',
                    ),
                    array(
                        'name' => 'DT9210 Digital Multimeter',
                        'why'  => 'Combines AC voltage, resistance and continuity checks for fault-finding on heating circuits.',
                    ),
                    array(
                        'name' => 'HTC-1',
                        'why'  => 'A simple, low-cost room temperature readout for quick ambient checks.',
                    ),
                ),
            ),
        ),

        'industrial-facility' => array(
            'what' => array(
                'heading' => 'What to Measure in Industrial Maintenance',
                'intro'   => 'Industrial and facility maintenance is mostly about confirming that power is where it should be and not where it shouldn\'t. A technician working on panels, motors and machines spends the day checking current flow, continuity and temperature.',
                'items'   => array(
                    'Check running current draw on a three-phase motor in a panel',
                    'Verify a breaker is carrying an overload before it trips',
                    'Trace a live feed inside a distribution board without breaking the circuit',
                    'Confirm a heater element pulls current during the heating cycle',
                    'Test for voltage on a machine isolator before opening the cover',
                    'Check continuity of the earthing conductor on a metal cabinet',
                    'Measure surface temperature on a motor housing to catch an overheat',
                ),
            ),
            'how' => array(
                'heading' => 'How to Check Current, Continuity and Voltage',
                'intro'   => 'A safe, repeatable routine keeps readings honest and hands out of harm\'s way. The same basic workflow carries across most panel, motor and machine checks.',
                'steps'   => array(
                    'Test the meter first. In AC voltage mode, prove the tester on a known live source, and re-prove it on the same source when you finish so a failed meter can never give a false safe reading.',
                    'For live current on a motor or machine, use the clamp jaws and close them around a single conductor, never around two phases, to read the true AC load.',
                    'To check a winding, fuse or breaker, de-energise the circuit first and use continuity or buzzer mode to confirm a near-zero reading.',
                    'For a low-voltage control or sensor circuit, select DC voltage mode, connect the black lead to the negative rail and read the working signal.',
                    'For a suspect contact or fuse, measure the voltage drop across it in live conditions instead of trusting a visual inspection.',
                ),
            ),
            'tips' => array(
                'heading' => 'Safety First',
                'intro'   => 'Electricity gives no second chances, so build the safe habits before the job starts. These few rules cover most panel and machine work.',
                'items'   => array(
                    'Never switch ranges while the clamp jaws are around a live conductor; keep both hands on the meter.',
                    'Stand on a dry insulating mat and keep the meter and leads clear of moisture.',
                    'De-energise and lock off the circuit where possible, then confirm it is dead with the meter before touching terminals.',
                    'At 230V wear insulated gloves and keep one hand out of the cabinet.',
                    'Hold leads only by the insulated finger guards and never touch the probe tips.',
                    'Check the meter CAT rating and inspect the leads for cracked insulation before any live work.',
                ),
            ),
            'recommended' => array(
                'heading' => 'Recommended Instruments',
                'intro'   => 'Four instruments cover the majority of panel, motor and machine checks on this site.',
                'items'   => array(
                    array(
                        'name' => 'VC3270 Digital Clamp Meter',
                        'why'  => 'Non-contact jaws read live motor and panel current without breaking the circuit, which suits daily machine checks.',
                    ),
                    array(
                        'name' => 'DT9210 Digital Multimeter',
                        'why'  => 'A general-purpose meter for continuity, resistance and AC/DC voltage checks across the whole installation.',
                    ),
                    array(
                        'name' => 'XT2017 Digital Clamp Meter',
                        'why'  => 'Use its clamp jaws to compare current across the phases of a three-phase motor and spot imbalance.',
                    ),
                    array(
                        'name' => 'XT101 Temperature Meter',
                        'why'  => 'Surface temperature readings flag an overloading motor, bearing or fuse before it fails.',
                    ),
                ),
            ),
        ),

        'led-appliance' => array(
            'what' => array(
                'heading' => 'What to Measure in LED and Appliance Repair',
                'intro'   => 'Before you open a lamp fitting or a domestic appliance, a short sequence of checks tells you where the fault really lives. Run these measurements in order and the repair usually narrows down to one component.',
                'items'   => array(
                    'Check the live-neutral polarity at the light fitting before reconnecting',
                    'Verify the supply voltage at the appliance inlet is actually present',
                    'Trace a lamp that stays dark by testing the switch in both positions',
                    'Measure AC voltage across the LED driver output terminals under load',
                    'Check continuity on a dead 230V outlet before suspecting the appliance',
                    'Confirm the earth path back to the panel on metal-bodied appliances',
                    'Detect intermittent drop-outs by watching the display during a continuity test',
                ),
            ),
            'how' => array(
                'heading' => 'How to Check Voltage and Continuity',
                'intro'   => 'A multimeter, used methodically, turns most lamp and appliance faults into a short checklist. Work the same routine every time so no step gets skipped.',
                'steps'   => array(
                    'Test the meter first. Plug the leads into the correct sockets, set the rotary switch to AC voltage mode, and confirm a known-good outlet reads mains voltage.',
                    'De-energise the circuit where you can and prove it is dead. Set continuity mode and touch the probes together to confirm the buzzer sounds before tracing anything.',
                    'Check the supply at the lamp or appliance. In AC voltage mode measure live to neutral and live to earth, and watch for the reading to sag when the load is switched on.',
                    'Isolate the suspect part with the power off: test the switch contacts, fuse and LED driver input in continuity mode.',
                    'Safely re-energise the circuit, then check the LED driver DC output across its output terminals while the lamp is connected, keeping the capacitor-discharge caution in mind.',
                    'Re-verify after the repair: confirm live-neutral polarity at the fitting and that the supply voltage holds before you close the unit back up.',
                ),
            ),
            'tips' => array(
                'heading' => 'Safety First',
                'intro'   => 'Voltage, current and a stored capacitor can hurt long after the switch is off. Keep these habits in front of you every time you pick up the meter.',
                'items'   => array(
                    'Discharge the smoothing capacitors inside an appliance power supply before probing the board — they can hold charge long after the switch is off.',
                    'De-energise the circuit where possible and verify it is dead before you touch anything.',
                    'At 230V use the one-hand technique and wear insulated gloves so you never become the path.',
                    'Never touch the probe tips while the meter is connected to a live circuit.',
                    'Re-check live-neutral polarity at the fitting after any repair before closing the unit.',
                    'Inspect the leads for cracked insulation and keep the meter dry at all times.',
                ),
            ),
            'recommended' => array(
                'heading' => 'Recommended Instruments',
                'intro'   => 'These meters cover the everyday checks above without the bulk and cost of a full lab bench.',
                'items'   => array(
                    array(
                        'name' => 'DT9210',
                        'why'  => 'A dependable all-round digital multimeter for the daily voltage and continuity checks on lamps and sockets.',
                    ),
                    array(
                        'name' => 'XT802',
                        'why'  => 'A practical second meter for quick polarity and live-neutral checks at the light fitting.',
                    ),
                    array(
                        'name' => 'DT181',
                        'why'  => 'A straightforward multimeter for tracing continuity and shorts through switches, fuses and driver boards.',
                    ),
                    array(
                        'name' => 'DT9208A',
                        'why'  => 'AC and DC voltage ranges cover supply feeds, switch checks and low-voltage driver outputs.',
                    ),
                ),
            ),
        ),
    );

    $result = isset( $content[ $slug ] ) ? $content[ $slug ] : array();
    if ( ! empty( $result ) ) {
        // 先按 EN 原文名给 recommended 条目挂上产品页映射(译名中型号代码逐字保留,
        // 映射以 EN 名为键,故必须在 localize 之前完成),再套语言字典。
        $result = ew_scenario_content_attach_products( $result );
        $result = ew_scenario_content_localize( $result, ew_scenario_content_dict() );
    }
    return $result;
}

/**
 * 按当前 Polylang 语言加载场景内容字典(inc/scenario-strings-{lang}.php,src→trans map)。
 * 英文(en)或未翻译语言返回空数组,调用方直接回退英文原文。
 */
function ew_scenario_content_dict() {
    static $dict = null;
    if ( null !== $dict ) {
        return $dict;
    }
    $dict = array();
    if ( function_exists( 'pll_current_language' ) ) {
        $lang = pll_current_language();
        if ( $lang && 'en' !== $lang ) {
            $file = __DIR__ . '/scenario-strings-' . $lang . '.php';
            if ( is_readable( $file ) ) {
                $map = require $file;
                if ( is_array( $map ) ) {
                    $dict = $map;
                }
            }
        }
    }
    return $dict;
}

/**
 * 递归替换 $data 中的字符串值:命中 dict 键则替换,否则原样。
 */
function ew_scenario_content_localize( $data, $dict ) {
    if ( is_string( $data ) ) {
        return isset( $dict[ $data ] ) ? $dict[ $data ] : $data;
    }
    if ( is_array( $data ) ) {
        $out = array();
        foreach ( $data as $k => $v ) {
            $out[ $k ] = ew_scenario_content_localize( $v, $dict );
        }
        return $out;
    }
    return $data;
}

/**
 * recommended 推荐条目 → 产品页(post_name)映射。键 = EN 原文名(译名中型号代码逐字保留)。
 * 每项为有序目标列表;token=null 表示整行一个链,否则行内每个型号按 token 单独成链。
 * 2026-09-04 与 EN 产品库逐条核对(page-scenario.php 的 Recommended Instruments 区块加内链)。
 */
function ew_scenario_recommended_map() {
    return array(
        'M100 / M200 Pen-Type Voltage Testers' => array(
            array( 'token' => 'M100',  'product' => 'm100-pen-type-voltage-tester' ),
            array( 'token' => 'M200',  'product' => 'm200-pen-type-voltage-tester' ),
        ),
        'VC3270 / VC3269 Digital Clamp Meters' => array(
            array( 'token' => 'VC3270', 'product' => 'vc3270-digital-clamp-meter' ),
            array( 'token' => 'VC3269', 'product' => 'vc3269-digital-clamp-meter' ),
        ),
        'Socket Tester Outlet Receptacle Tester' => array(
            array( 'token' => null, 'product' => 'socket-tester-series' ),
        ),
        'DT6221 Digital Meter' => array(
            array( 'token' => null, 'product' => 'dt6221-series' ),
        ),
        'Wilk 302 / 303 Car Electric Pens' => array(
            array( 'token' => 'Wilk 302', 'product' => 'wilk302-series' ),
            array( 'token' => '303',      'product' => 'wilk303-series' ),
        ),
        'HW170 / HW171 / HW172 Battery Meters' => array(
            array( 'token' => 'HW170', 'product' => 'hw170-series' ),
            array( 'token' => 'HW171', 'product' => 'hw171-series' ),
            array( 'token' => 'HW172', 'product' => 'hw172-series' ),
        ),
        'HW304–HW310 Series' => array( // 范围行(省略号,无法逐字拆),整行链首款 hw304 系列页
            array( 'token' => null, 'product' => 'hw304-series' ),
        ),
        'HW65 / HW68 / HW80 Series' => array(
            array( 'token' => 'HW65', 'product' => 'hw65-series' ),
            array( 'token' => 'HW68', 'product' => 'hw68-series' ),
            array( 'token' => 'HW80', 'product' => 'hw80-series' ),
        ),
        'BT-168 Series' => array(
            array( 'token' => null, 'product' => 'bt-168-series' ),
        ),
        'HW171 / HW172 Battery Meters' => array(
            array( 'token' => 'HW171', 'product' => 'hw171-series' ),
            array( 'token' => 'HW172', 'product' => 'hw172-series' ),
        ),
        'HTC-1' => array(
            array( 'token' => null, 'product' => 'htc-1-temperature-meter' ),
        ),
        'XT300 Temperature Meters' => array(
            array( 'token' => null, 'product' => 'xt300-temperature-meter' ),
        ),
        'VC3270 Digital Clamp Meter' => array(
            array( 'token' => null, 'product' => 'vc3270-digital-clamp-meter' ),
        ),
        'DT9210 Digital Multimeter' => array(
            array( 'token' => null, 'product' => 'dt9210-digital-multimeter' ),
        ),
        'XT2017 Digital Clamp Meter' => array(
            array( 'token' => null, 'product' => 'xt2017-series' ),
        ),
        'XT101 Temperature Meter' => array(
            array( 'token' => null, 'product' => 'xt101-temperature-meter' ),
        ),
        'DT9210' => array(
            array( 'token' => null, 'product' => 'dt9210-digital-multimeter' ),
        ),
        'XT802' => array(
            array( 'token' => null, 'product' => 'xt802-digital-multimeter' ),
        ),
        'DT181' => array(
            array( 'token' => null, 'product' => 'dt181-series' ),
        ),
        'DT9208A' => array(
            array( 'token' => null, 'product' => 'dt9208-series' ),
        ),
    );
}

/**
 * 给 $data['recommended']['items'] 每项挂 'product'(整行主链)与 'links'(逐型号链)映射。
 * 必须在 ew_scenario_content_localize() 之前调用(键是 EN 原文名)。
 */
function ew_scenario_content_attach_products( $data ) {
    if ( empty( $data['recommended']['items'] ) || ! is_array( $data['recommended']['items'] ) ) {
        return $data;
    }
    $map = ew_scenario_recommended_map();
    foreach ( $data['recommended']['items'] as $i => $item ) {
        $key = isset( $item['name'] ) ? (string) $item['name'] : '';
        if ( '' === $key || ! isset( $map[ $key ] ) ) {
            continue;
        }
        $links   = array();
        $primary = '';
        foreach ( $map[ $key ] as $t ) {
            $slug = isset( $t['product'] ) ? (string) $t['product'] : '';
            if ( '' === $slug ) {
                continue;
            }
            if ( '' === $primary ) {
                $primary = $slug; // 首个目标同时作为兜底主链
            }
            if ( isset( $t['token'] ) && '' !== (string) $t['token'] ) {
                $links[] = array( 'token' => (string) $t['token'], 'product' => $slug );
            }
        }
        $item['product'] = $primary;
        if ( ! empty( $links ) ) {
            $item['links'] = $links;
        }
        $data['recommended']['items'][ $i ] = $item;
    }
    return $data;
}

/**
 * 产品页 EN post_name → 当前语言 permalink(与 ew_scenario_product_ids 同逻辑:
 * 非 EN 语言先经 pll_get_post 映射到译名产品页,无译名回退 EN 原页)。
 */
function ew_scenario_product_url( $en_slug ) {
    static $cache = array();
    if ( function_exists( 'pll_current_language' ) ) {
        $lang = pll_current_language();
    } else {
        $lang = '';
    }
    $ckey = (string) $en_slug . '|' . (string) $lang;
    if ( array_key_exists( $ckey, $cache ) ) {
        return $cache[ $ckey ];
    }
    $cache[ $ckey ] = '';
    global $wpdb;
    $pid = (int) $wpdb->get_var( $wpdb->prepare(
        "SELECT ID FROM {$wpdb->posts} WHERE post_type='product' AND post_status='publish' AND post_name=%s LIMIT 1",
        (string) $en_slug
    ) );
    if ( ! $pid ) {
        return '';
    }
    if ( function_exists( 'pll_get_post' ) && $lang && function_exists( 'pll_default_language' ) && $lang !== pll_default_language() ) {
        $tr = pll_get_post( $pid, $lang );
        if ( $tr ) {
            $pid = (int) $tr;
        }
    }
    $url = get_permalink( $pid );
    $cache[ $ckey ] = is_string( $url ) ? $url : '';
    return $cache[ $ckey ];
}

/**
 * 把推荐名渲染成带产品链接的 HTML(已转义,直接 echo)。
 * - 单目标:整行名包一个 <a>;
 * - 多型号:在名字里把每个型号 token 单独包成 <a>(译名中代码逐字保留);
 * - 兜底:某个 token 在当前语言没找到时,退回整行主链。
 */
function ew_scen_kit_name_html( $name, $kit ) {
    $name = is_string( $name ) ? $name : '';
    if ( '' === $name ) {
        return '';
    }
    $primary = isset( $kit['product'] ) ? (string) $kit['product'] : '';
    $links   = ( isset( $kit['links'] ) && is_array( $kit['links'] ) ) ? $kit['links'] : array();

    if ( empty( $links ) && '' !== $primary ) {
        $url = ew_scenario_product_url( $primary );
        if ( '' !== $url ) {
            return '<a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a>';
        }
        return esc_html( $name );
    }

    // 逐型号:收集每个 token 在名字里的出现区间(字节偏移;token 全为 ASCII,不截断多字节)。
    $spans = array();
    foreach ( $links as $lk ) {
        $tok = isset( $lk['token'] ) ? (string) $lk['token'] : '';
        $slg = isset( $lk['product'] ) ? (string) $lk['product'] : '';
        if ( '' === $tok || '' === $slg ) {
            continue;
        }
        $url = ew_scenario_product_url( $slg );
        if ( '' === $url ) {
            continue;
        }
        $tlen = strlen( $tok );
        $pos  = 0;
        while ( ( $p = strpos( $name, $tok, $pos ) ) !== false ) {
            $spans[] = array( 's' => $p, 'e' => $p + $tlen, 'u' => $url );
            $pos     = $p + $tlen;
        }
    }

    if ( empty( $spans ) ) {
        // 本语言名字里找不到型号代码(异常兜底):整行主链。
        $url = '' !== $primary ? ew_scenario_product_url( $primary ) : '';
        if ( '' !== $url ) {
            return '<a href="' . esc_url( $url ) . '">' . esc_html( $name ) . '</a>';
        }
        return esc_html( $name );
    }

    usort( $spans, function ( $a, $b ) { return $a['s'] - $b['s']; } );
    $out = '';
    $cur = 0;
    foreach ( $spans as $sp ) {
        if ( $sp['s'] < $cur ) { // 覆盖冲突时保留先出现的
            continue;
        }
        $out .= esc_html( substr( $name, $cur, $sp['s'] - $cur ) );
        $out .= '<a href="' . esc_url( $sp['u'] ) . '">' . esc_html( substr( $name, $sp['s'], $sp['e'] - $sp['s'] ) ) . '</a>';
        $cur = $sp['e'];
    }
    $out .= esc_html( substr( $name, $cur ) );
    return $out;
}
