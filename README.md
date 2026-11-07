# Jofme Premium

WordPress/WooCommerce theme, version 1.7.3. Theme files live at the repository root.

## Εγκατάσταση

Κατεβάστε το **jofme.zip** από τα Releases, όχι το αυτόματο “Source code” ZIP του GitHub. Στο WordPress: Εμφάνιση → Θέματα → Προσθήκη → Μεταφόρτωση. Ο φάκελος του theme πρέπει να ονομάζεται `jofme`.

## Ενημερώσεις από WordPress

1. Το repository πρέπει να είναι **δημόσιο** για τον ενσωματωμένο updater.
2. Εμφάνιση → Jofme Updates: αποθηκεύστε `owner/repository`.
3. Στις επόμενες εκδόσεις: Πίνακας ελέγχου → Ενημερώσεις → Έλεγχος ξανά → Ενημέρωση θέματος.
4. Ο έλεγχος GitHub αποθηκεύεται για μία ώρα. Η επαναποθήκευση της ρύθμισης καθαρίζει την cache.

Το αρχικό theme 1.0.0 δεν έχει updater: εγκαταστήστε μία φορά το νέο ZIP. Για ιδιωτικό repository χρειάζεται διαφορετικός μηχανισμός πιστοποιημένης διανομής. Μην προσθέτετε tokens στον κώδικα. Οι αλλαγές στον κώδικα του γονικού theme αντικαθίστανται στις ενημερώσεις· χρησιμοποιήστε child theme για τοπικές προσαρμογές.

## Δημοσίευση νέας έκδοσης

Αλλάξτε την έκδοση στα `style.css` και `functions.php`, κάντε commit/push και δημιουργήστε tag `v1.1.1` (ή την αντίστοιχη έκδοση). Το GitHub Actions ελέγχει PHP/JavaScript, επιβεβαιώνει τις εκδόσεις και δημιουργεί Release με `jofme.zip`. Απαιτούνται ενεργά Actions στο repository. Το push στο main εκτελεί μόνο ελέγχους και φτιάχνει artifact· για ενημέρωση WP χρειάζεται tag/release.

## Κατάστημα

- Εμφάνιση → Προσαρμογή → Jofme Options → Shop: Minimal, Framed ή Editorial κάρτα και αρχική προβολή 1/2/3 στηλών ή λίστας.
- Η επιλογή προβολής του επισκέπτη αποθηκεύεται στον browser. Σε μικρές οθόνες οι 3 στήλες γίνονται 2.
- Attributes: χρησιμοποιήστε τα καθολικά attributes του WooCommerce. Τα φίλτρα εφαρμόζονται με κανονικό GET request και ανανέωση σελίδας, μαζί με τιμή και collection από product tags.
- **Color swatches:** Προϊόντα → Χαρακτηριστικά → επεξεργασία attribute → ενεργοποιήστε **Display as color swatches**. Στη Διαμόρφωση όρων ορίστε χρώμα με color picker ή hex. Εμφανίζονται στις κάρτες προϊόντων, ως κύκλοι στα φίλτρα καταλόγου και ως επιλογές παραλλαγής στη σελίδα προϊόντος. Χωρίς ρητή ρύθμιση, attributes με slug `color` / `colour` / `finish` / `metal` / `χρωμ` συνεχίζουν να λειτουργούν ως swatches. Άγνωστο χρώμα εμφανίζεται ως ετικέτα κειμένου.
- Quick view με εικόνα, τιμή, σύντομη περιγραφή και διαθεσιμότητα. Η επιλογή παραλλαγής και η αγορά γίνονται στη σελίδα προϊόντος.
- Εικονίδια κάρτας: quick view και σύνδεσμος λεπτομερειών.

## Έλεγχος πριν από παραγωγή

Απαιτείται staging WordPress με WooCommerce και πραγματικά προϊόντα: μεταβλητά προϊόντα, συνδυασμός φίλτρων, κατηγορίες, σελιδοποίηση, φόροι/νόμισμα, κινητό, πληκτρολόγιο, Elementor/WPML και ενημέρωση από προηγούμενη έκδοση. Οι έλεγχοι σύνταξης δεν αντικαθιστούν αυτά τα integration tests.

Τοπικό πακέτο: `python3 scripts/package.py`. PHP 7.4+, WordPress 6.4+. GPL-2.0-or-later όπως το αρχικό theme.

## Layout Settings (1.2.0)

Εμφάνιση → Προσαρμογή → Jofme Options → Layout Settings. Ξεχωριστές επιλογές για Pages, Blog categories, Shop & product categories και Product pages: πλάτος Boxed / Wide / Narrow / Full width και sidebar None / Left / Right. Στα προϊόντα υπάρχει και Gallery left / Gallery right / Gallery above summary.

Προσθέστε widgets στις περιοχές Page Sidebar, Blog Sidebar, Shop Sidebar ή Product Sidebar αντίστοιχα. Χωρίς widgets δεν μένει κενή στήλη. Σε κινητό το sidebar ακολουθεί το περιεχόμενο. Οι επιλογές αφορούν τα templates του theme· τα Elementor Canvas/Full Width templates διατηρούν τον έλεγχο του Elementor. Οι στατικές αρχικές σελίδες χρησιμοποιούν τις ρυθμίσεις Pages.

## Εναλλαγή εικόνας στο hover (1.2.1)

Εμφάνιση → Προσαρμογή → Jofme Options → Shop → **Change product image on hover**. Προεπιλογή: απενεργοποιημένο. Χρησιμοποιείται η πρώτη έγκυρη εικόνα της gallery που διαφέρει από την κύρια. Εφαρμόζεται στις κάρτες προϊόντων, μαζί με τις προβολές grid/list και τις related products. Χωρίς δεύτερη εικόνα ή αν αποτύχει η φόρτωσή της παραμένει η κύρια. Η εναλλαγή λειτουργεί και με keyboard focus· στις συσκευές αφής διατηρείται η κύρια φωτογραφία και η κανονική πλοήγηση.

## Editorial header & Elementor widgets (1.3.0)

Διαθέσιμα από την έκδοση 1.3.0.

- **Header:** Εμφάνιση → Προσαρμογή → Jofme Options → Header → Header layout → Editorial. Οι θέσεις μενού “Header utility left” και “Header utility right” ρυθμίζονται στο Εμφάνιση → Μενού. Υπάρχουν επιλογές utility bar και transparent header στη στατική αρχική σελίδα. Το transparent ενεργοποιείται όταν η αρχική ξεκινά με μεγάλη φωτογραφία/hero. Sticky header από την υπάρχουσα ρύθμιση. Account και cart χρησιμοποιούν WooCommerce, η αναζήτηση ανοίγει το πραγματικό search form. Οι γλώσσες εμφανίζονται όταν λειτουργεί WPML.
- **Elementor → κατηγορία Jofme → Editorial Story (Paola Dias):** μεγάλη εικόνα, τίτλος, εισαγωγή, δεύτερη εικόνα, περιγραφή και link συλλογής. Επιλογή εικόνας αριστερά/δεξιά, χρώματα, τυπογραφία, responsive padding και μέγεθος detail image.
- **Elementor → κατηγορία Jofme → Weekly Sales:** τίτλος section, πραγματικό προϊόν WooCommerce, κεντρική καμπάνια με εικόνα/overlay/κείμενο/link και δεξιά στήλη κειμένου/εικόνας. Product ID 0 = αυτόματη επιλογή προϊόντος σε έκπτωση. Μπορεί να οριστεί συγκεκριμένο δημοσιευμένο/ορατό product ID. Η τιμή προέρχεται από το WooCommerce· badge προσφοράς μόνο όταν υπάρχει πραγματική έκπτωση.
- Χωρίς διαθέσιμο προϊόν, το δημόσιο section προσαρμόζεται σε δύο στήλες και ο editor εμφανίζει οδηγία. Χωρίς Elementor το theme συνεχίζει να λειτουργεί χωρίς να φορτώνει τις κλάσεις widgets. Δεν απαιτείται Elementor Pro.
- Επιλέξτε δικές σας εικόνες από τη βιβλιοθήκη πολυμέσων. Δεν περιλαμβάνονται εικόνες ή αρχεία από το demo αναφοράς. Για full-bleed section χρησιμοποιήστε full-width Elementor container και την αντίστοιχη ρύθμιση πλάτους στο theme· τα Elementor Canvas templates ελέγχονται από το Elementor.

Έλεγχοι: PHP/JS syntax, tests rendering/escaping/product visibility, πραγματικό runtime WordPress 7.1.4 + Elementor 4.3.6 + WooCommerce 11.2.2, και browser tests στα 390/768/1024/1440px για layout, mobile menu, focus, search και sticky header. Ο τελικός έλεγχος στο περιβάλλον του καταστήματος παραμένει απαραίτητος πριν τη δημοσίευση.

## Gallery design & Typography (1.4.0)

- **Διόρθωση hover:** η δεύτερη εικόνα παραμένει πάνω από την κύρια μετά το fade, με ρητό z-index μέσα σε απομονωμένο container. Η κύρια παραμένει διαθέσιμη αν δεν φορτωθεί η δεύτερη.
- **Κάρτες:** Προσαρμογή → Jofme Options → Shop → Product card layout → **Gallery (minimal product grid)**. Λευκή κάρτα με στρογγυλεμένες γωνίες, εικόνα σε contain, κατηγορία και μικρό sans-serif τίτλο/τιμή. Quick view και λεπτομέρειες εμφανίζονται στο hover/focus και παραμένουν προσβάσιμα στο κινητό. Η προβολή list συνεχίζει να λειτουργεί.
- **Σελίδα προϊόντος:** Προσαρμογή → Jofme Options → Layout Settings → Product pages — Design → **Gallery**. Κάθετες stacked φωτογραφίες αριστερά με scroll, sticky purchase panel δεξιά, πλήκτρα ποσότητας, collapsible Description / Care instructions. Στο κινητό το purchase panel εμφανίζεται κάτω από τις φωτογραφίες. Οι επιλογές παραλλαγών, τιμές, αλλαγή κύριας εικόνας στην παραλλαγή, έλεγχος αποθέματος και add to cart παραμένουν λειτουργίες WooCommerce.
- **Οδηγίες φροντίδας:** επεξεργασία προϊόντος → Δεδομένα προϊόντος → Γενικά → Care instructions. Εμφανίζονται μόνο όταν υπάρχει κείμενο.
- **Γραμματοσειρές:** Προσαρμογή → Jofme Options → Typography. Ξεχωριστές επιλογές για Text & navigation και Headings. Η **Aeonik Pro Light (CDN)** χρησιμοποιεί το stylesheet του OnlineWebFonts που δόθηκε για το project. Φορτώνεται μόνο όταν επιλεγεί. Εναλλακτικά υπάρχουν Manrope, Cormorant, system fonts και custom Aeonik Pro με δικό σας WOFF/WOFF2 upload ή direct CDN URL για regular/medium/bold. Αν ο πάροχος CDN δεν είναι διαθέσιμος, χρησιμοποιείται fallback font.

Τα υπάρχοντα επιλεγμένα layouts παραμένουν. Για εμφάνιση αντίστοιχη των references, επιλέξτε Gallery στα δύο layouts και Aeonik Pro Light στη Typography. Η διόρθωση hover εφαρμόζεται σε όλα τα card layouts.

Έλεγχοι: visual regression που ελέγχει το πραγματικό χρώμα του εμφανιζόμενου image μετά το fade (grid/list), πραγματικό variable product με αλλαγή παραλλαγής/εικόνας/τιμής και ποσότητας, native add to cart, CDN font και responsive widths 390/768/1024/1440px.

## Bestsellers card layout + hover (1.7.1 / 1.7.2)

- Εμφάνιση → Προσαρμογή → Jofme Options → Shop → **Product card layout → Bestsellers (tabs card)** (προεπιλογή).
- Ίδιο card look σε shop, categories, related/upsells και στο Elementor Bestsellers Tabs widget.
- Κατηγορία, τίτλος, τιμή και κυκλικό % sale badge.
- Εμφάνιση → Jofme Options → Shop → **Change product image on hover** για εναλλαγή δεύτερης εικόνας στις κάρτες (και στο widget).
- 1.7.2 διορθώνει το overlay των hover εικόνων (χωρίς κενό κάτω από την κύρια εικόνα).
- 1.7.3 διορθώνει τις εικόνες που κόβονταν έξω από το frame της κάρτας.

## Bestsellers Tabs Elementor widget (1.7.0)

Elementor → κατηγορία **Jofme** → **Bestsellers Tabs**.

- Τίτλος section (προεπιλογή: “Bestsellers for a Reason”).
- Tabs με underline active state: προαιρετικό “All” + repeater κατηγοριών WooCommerce.
- Grid προϊόντων 2/3/4 στηλών, όριο προϊόντων, order by popularity/date/price.
- Κάρτα με εικόνα, κατηγορία, τίτλο, τιμή και % sale badge.
- Προαιρετικό “View all” link.
- Keyboard-accessible tabs (βέλη / Home / End). Δεν απαιτεί Elementor Pro.

## Global scroll speed (1.6.1)

Εμφάνιση → Προσαρμογή → Jofme Options → Header:

- **Smooth page scroll:** ενεργό από προεπιλογή.
- **Scroll speed:** Slow / Medium / Fast.
- Χρησιμοποιεί πραγματικό window scroll (τα sticky panels παραμένουν σωστά).
- Σέβεται `prefers-reduced-motion` και δεν παρεμβαίνει σε nested scroll areas ή σε touch drag.
- Τα in-page anchors, back-to-top και gallery dots χρησιμοποιούν την ίδια ταχύτητα.

## Attribute color swatches & product scroll gallery (1.6.0)

Διαθέσιμα από την έκδοση 1.6.0.

- Προϊόντα → Χαρακτηριστικά: checkbox **Display as color swatches** ανά attribute.
- Διαμόρφωση όρων: native color picker + hex πεδίο (`jofme_swatch_color`).
- Κατάλογος: χρωματιστά φίλτρα (radio swatches) αντί για dropdown στα color attributes.
- Κάρτες προϊόντων: swatch links προς την παραλλαγή.
- Σελίδα προϊόντος: swatches αντί για dropdown στις παραλλαγές· το native select παραμένει για το WooCommerce variation JS.
- Ρητό `no` απενεργοποιεί τα swatches ακόμα και σε color slug· κενή ρύθμιση διατηρεί το προηγούμενο slug heuristic.
- **Gallery product scroll:** στο Product pages — Design → Gallery, οι εικόνες στοιβάζονται κάθετα και κάνουν scroll· το purchase panel μένει sticky δεξιά (όπως στο Minas reference). Dots πλοήγησης με scroll-spy στο desktop· στο κινητό οι εικόνες μένουν stacked full-width και το panel ακολουθεί από κάτω.

## Covers, view controls και διορθώσεις layouts (1.5.0)

Διαθέσιμα από την έκδοση 1.5.0.

- Επιλογέας στηλών με SVG κάθετες γραμμές για 1/2/3/4 στήλες και εικονίδιο λίστας. Η προβολή 4 στηλών διατηρείται στον browser και προσαρμόζεται σε 2 στο κινητό.
- Η επικεφαλίδα του καταλόγου εκτείνεται πάνω από το grid και το sidebar, αριστερό ή δεξί.
- Προϊόντα → Κατηγορίες → Προσθήκη/Επεξεργασία → **Category cover**: ξεχωριστή εικόνα cover πίσω από την επικεφαλίδα, με native Media Library, preview και αφαίρεση. Δεν αντικαθιστά το category thumbnail. Χωρίς cover παραμένει απλή επικεφαλίδα.

### Review συνοχής layouts

Διορθώθηκαν συγκρούσεις που εντοπίστηκαν σε πραγματική σελίδα προϊόντος και σε local WordPress/WooCommerce:

- Το αποθηκευμένο grid/list view εφαρμόζεται μόνο στα archives με επιλογέα, όχι στα related/upsell προϊόντα ή στο single product.
- Η Gallery gallery και το purchase panel έχουν κοινό intro container, ώστε το sticky purchase panel να μη συνεχίζει πάνω από tabs και related products.
- Οι κανόνες διαστάσεων/padding της Gallery εφαρμόζονται στις φωτογραφίες των slides, όχι στο native zoom overlay.
- Τα swatch links βρίσκονται έξω από το κύριο product link, αποφεύγοντας άκυρα nested anchors.
- Οι βασικές στήλες προκύπτουν από τα WooCommerce loop classes. Related products και shortcodes διατηρούν δική τους διάταξη και responsive συμπεριφορά.
- Υπάρχει μία ρύθμιση Default catalog view στα Jofme Options. Οι παλιές τιμές στηλών διαβάζονται ως fallback, χωρίς δεύτερο αντικρουόμενο control.
- Διορθώθηκαν οι grid θέσεις και το κεντράρισμα του Centered header, καθώς και η συμπεριφορά μεγάλου site title στο κινητό.
- Το sticky header και τα Gallery controls χρησιμοποιούν τα κοινά color tokens. Το ύψος του Editorial header δηλώνεται στο κοινό header-height token.
- Το announcement text ανανεώνει σωστά το preview του Customizer.

Έλεγχοι: 3 headers σε desktop/mobile, 4 card layouts × 3 sidebar positions × desktop/mobile, Classic galleries left/right/stacked και Gallery product design, όριο sticky στο scroll, native zoom, cover heading, 4-col/list controls, swatches και μηδενικά JavaScript errors στον συνδυαστικό έλεγχο. Το cover ελέγχθηκε και με το πραγματικό WordPress media modal, ενώ η αποθήκευση/αφαίρεση ελέγχθηκε με έγκυρο και άκυρο nonce.
