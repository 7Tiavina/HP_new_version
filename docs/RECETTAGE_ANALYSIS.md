# Analysis: HelloPassenger recettage documents (EN)

Summary of **Rec_Home_Page_EN.docx** and **Rec_About_Us_EN.docx** — client feedback and content specs for the site.

---

## 1. Rec_Home_Page_EN.docx — Home page recettage

### UI / UX issues
| Item | Feedback |
|------|----------|
| **Cart** | Opens behind the “Book now” button. |
| **Discover** | Button is white and inactive. |
| **Book now (header)** | Stays white for a few seconds after use. |
| **Book now under “Travel solutions”** | Redundant; the one under travel solutions is highlighted (black). Clarify why two. |
| **“Travel light, luggage free” block** | “Ce n’est pas très réussir” — not very successful; needs rework. |
| **Transport of luggage** | Button opens a **new tab** (explanation page). |
| **Luggage storage** | Button goes to **same tab** → booking page (not explanation page). Align behaviour (new tab vs same tab). |
| **Learn more** | Does not open a new tab (should it?). |

### Content / visuals
| Item | Feedback |
|------|----------|
| **Experience 25+ (2001)** | Don’t like the young girl photo; prefer **airport passenger** photos. |
| **Runway / hero image** | Prefer **warm colours**; e.g. planes on runways at CDG, passengers with luggage at airport at **sunset** (current plane + blue icon clashes). |
| **Services section** | Still **no “Transport of luggage”** box; it’s one of the two main services. Meeting agreed a **“location” (rental)** section. |
| **Our process** | Use a **screenshot of the home in a laptop** to make it feel more real; use more **premium** photos overall. **Porthole (hublot)** idea is liked but “un hublot c’est comme ça” — consider hiding until correct photos. |

### Copy to integrate (EN)
- **Reserve** — “Complete your reservation in just a few steps through our secure platform. Your digital voucher is instantly issued and accessible from your email and personal account.”
- **Choose Your service** — “Decide how your luggage is handled. Drop off at our dedicated airport facility, benefit from a personalised meet & collect service, or arrange coordinated transport to/from the airport. Each option for maximum convenience and flexibility.”
- **Travel with Confidence** — “Move freely while we take care of your luggage. Handled with professionalism, discretion and the highest standards of security, your luggage is managed smoothly from start to finish.”
- **Testimonial** — “Trusted by Travelers Worldwide” / HelloPassenger chosen by travelers; reliability, security, elevated service.

### Other
- **Reassurance block** (testimonials/reviews): pay attention to presentation.
- **Google review**: HP at 3.4 because “left luggage” listing was moved to HP; need to **revisit** this.
- **Footer**: Add **WBN member** logo (white version).
- **contact@hellopassenger.com** — **does not work**; fix or replace.

---

## 2. Rec_About_Us_EN.docx — About Us recettage

### Structure
- **Two sections** “What to do” and “Useful information” must be **integrated into the FAQ** following the last meeting.

### Positioning (to keep)
- **Rooted in Paris Airports. Driven by People.**
- HelloPassenger operated by **Bagages du Monde**, official partner of **Aéroports de Paris (GROUPE ADP)** since 2003.
- Over twenty years at **Paris-Charles de Gaulle (CDG)** and **Paris-Orly (ORY)**.

### Security / trust (to keep)
- **Security You Can See. People You Can Trust.**
- Every item: **100% X-ray control**; **CCTV-monitored, alarm-protected** storage; **controlled access**; **fully traceable**.
- Procedures in line with **CSI (Code de sécurité intérieure)**.
- **Photos to add**: (1) one or both Paris airports; (2) **X-ray** (stock until new agency photos).

### Culture / team
- **A Culture of Responsibility** — uniforms, ID, security clearance, continuous training, attentiveness.
- **Meet the People Behind the Service** / **The Professionals Who Make It Possible** — teams at CDG and ORY; trained, certified, security-cleared; “trust is built face to face.”

### Visuals / “hublot”
- **Slider in the porthole (hublot)** — hide until correct photos are available.
- **Photo 1**: Enzo in front of RX + short line on “sécurité et 20 ans d’expérience”.
- **Photo 2**: Momo in interaction with client + short line on “expérience client”.
- **Photo 3**: Storage zone with CCTV.

### Why choose us (short)
- 100% X-ray / CCTV / 20+ trained people / trusted by travelers.

---

## 3. Photos: what we have vs what recettage asks

**Checked:** The two .docx files contain **embedded images** (Word binary in `word/media/`). Those were not extracted here. The **live site assets** in the project are below.

### In project (Hostinger theme + uploads)
| Path / description | Recettage use |
|--------------------|----------------|
| `wp-content/uploads/2025/11/CDG-airport.jpg` | Airport photo — fits “one or both Paris airports”. |
| `wp-content/uploads/2025/11/happy-woman-plane-ticket-and-smile-for-travel-f-logo-500x635-1.jpg` | Likely the “young girl” photo to **replace** with airport passengers. |
| `wp-content/uploads/2025/11/design-of-modern-workspace-at-home-500x635-2.jpg` | Workspace — not airport. |
| `wp-content/themes/travivu/assets/images/plane.png` | Plane icon — recettage says plane + blue icon “dénote”; consider warmer hero. |
| `public/rayonx.png` (root) | X-ray placeholder — can be used as **stock X-ray** until new agency photos. |
| **Enzo / Momo / CCTV zone** | **Not in project** — recettage: “hide until correct photos”; add when client provides. |

### Recettage asks for (not yet in repo)
- **Experience 25+**: Replace young girl with **airport passenger** photos.
- **Hero / runway**: **Warm colours** — e.g. planes on runways at CDG, passengers with luggage at **sunset** (new assets or stock).
- **About Us**: (1) Photo of one or both Paris airports — **CDG-airport.jpg** can be used; (2) **X-ray** — **rayonx.png** or stock until new agency; (3) **Team slider** (Enzo, Momo, CCTV) — to be added when client supplies images.

So: **yes, photos were checked.** Current assets can cover airport + X-ray placeholders; hero and team shots need client-provided or new stock images.

---

## 4. Action summary (for dev / content)

1. **Fix**: Contact email `contact@hellopassenger.com` (link or address).
2. **UX**: Align “Transport of luggage” vs “Luggage storage” (new tab vs same tab); “Learn more” behaviour.
3. **Home**: Add “Transport of luggage” / “location” in Services; consider laptop mockup + warmer, premium hero/runway images; review Discover and double Book now.
4. **About**: Add “What to do” and “Useful information” into FAQ; add airport + X-ray images; add team slider (Enzo, Momo, CCTV zone) and “Why choose us” bullets; WBN logo (white) in footer.
5. **Copy**: Use the EN paragraphs above for Reserve, Choose Your service, Travel with Confidence, and testimonial block.

---

## 5. Implemented so far

- **Contact email**: Fixed. The footer in the Hostinger `index.html` had `mailto:%20contact@hellopassenger.com` (leading space). Replaced with `mailto:contact@hellopassenger.com`. The Laravel `footer-front.blade.php` contact line is now a proper `mailto:` link.
- **Photos**: Documented above; no asset changes until you provide or choose new hero/team images.
- **Step 1 — Learn More**: Link now opens in a new tab (`target="_blank" rel="noopener noreferrer"`).
- **Step 2 — Discover**: Changed from plain text to a link that scrolls to the “Travel Light, Luggage Free” section (`#travel-light`). Added visible style (yellow accent, border, hover) so it no longer looks “white and inactive”.
- **Step 3 — Book now header**: Added script on home so that after clicking any “Book now” / form link, focus is cleared after 350 ms so the button doesn’t stay in the “white” focus state.
- **Step 4 — Our Process copy**: Replaced the three blocks with recettage EN copy: **Reserve** (voucher / secure platform), **Choose Your service** (drop-off, meet & collect, transport), **Travel with Confidence** (professionalism, security, smooth handling). The third block link now opens in a new tab.
- **Step 5 — Testimonial**: Title set to “Trusted by Travelers Worldwide” and subtitle updated to the recettage paragraph (international visitors, reliability, security, elevated service).

---

## 6. Implemented (remaining batch)

- **Cart z-index**: Cart dropdown and overlay now use higher z-index so the cart opens above the Book now button.
- **Redundant Book now**: Removed the header BOOK NOW button next to PROMOHIVER; hero and promo-bar Book now links remain.
- **Travel light block**: Added subtitle: From airport drop-off to meet & collect or transport — we've got you covered.
- **Services section**: Added **Transport of Luggage** as the first service box (CDG-airport.jpg, link to transport-of-luggage in new tab).
- **Footer WBN**: Added Member of WBN text (white); replace with white logo image when asset is available. About Us (FAQ, images, team slider) for later.
- **Hero/runway images**: Replace with warmer, premium or airport-passenger assets when provided.
- **Google review** strategy (3.4 rating note).

## 7. Implemented (About Us & FAQ)
- **About Us** (`/about-us`): Laravel page with Hostinger shell. Content: Rooted in Paris Airports; Bagages du Monde / ADP; Security You Can See (100% X-ray, CCTV, CSI); Culture of Responsibility; team slider (Enzo, Momo, CCTV placeholders); Why Choose Us. Airport image uses `../wp-content/uploads/2025/11/CDG-airport.jpg`; X-ray uses `rayonx.png` if present in `public/`.
- **FAQ** (`/faq`): Laravel page with sections “What to do” and “Useful information before flying” (integrated per recettage). Links from home, payment, **formulaire-consigne (link-form)**, and shell header/footer now point to these Laravel routes.
- **Team slider**: About Us has a simple prev/next + dots slider; replace the placeholder divs inside each `.about-team-slide` with `<img>` when Enzo, Momo, CCTV photos are available.

## 8. Still for later (content or assets only)
- Footer: replace WBN text with white logo image when you have it. Hero/runway and Google review as above.
