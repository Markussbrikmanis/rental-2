const plans = [
    {
        name: 'Starter',
        price: 'EUR 19',
        period: '/month',
        description: 'For small landlords who want one clean place for leases, invoices, and tenant communication.',
        features: ['Up to 10 units', 'Invoice history', 'Tenant portal access', 'Email reminders'],
    },
    {
        name: 'Growth',
        price: 'EUR 49',
        period: '/month',
        description: 'For active property managers who need recurring billing, meter tracking, and exports.',
        features: ['Up to 75 units', 'Automated recurring charges', 'Meter readings', 'CSV and PDF exports'],
        featured: true,
    },
    {
        name: 'Scale',
        price: 'Custom',
        period: '',
        description: 'For larger portfolios that need tailored onboarding, support, and subscription control.',
        features: ['75+ units', 'Priority support', 'Custom onboarding', 'Admin-level controls'],
    },
];

const features = [
    {
        eyebrow: 'Operations',
        title: 'Keep leases, tenants, and invoices in one place.',
        copy: 'This SaaS is built for rental operations: properties, units, lease agreements, invoices, payments, and usage-based charges are managed from a single workflow.',
    },
    {
        eyebrow: 'Billing',
        title: 'Generate predictable monthly billing.',
        copy: 'Owners can define recurring charge rules, issue invoices, track payment status, and export documents without moving data between spreadsheets and email threads.',
    },
    {
        eyebrow: 'Tenant Experience',
        title: 'Give tenants a simple self-service portal.',
        copy: 'Tenants can log in, review their contracts, download invoices, and submit meter readings from a focused portal instead of messaging back and forth.',
    },
    {
        eyebrow: 'Reporting',
        title: 'Move from manual reconciliation to live reporting.',
        copy: 'The platform includes reporting and export flows so owners can understand revenue, outstanding balances, and billing history faster.',
    },
];

const privacyItems = [
    'Account data is used to operate property management, billing, and support workflows.',
    'Tenant and invoice records are stored only for service delivery, audit history, and legal accounting requirements.',
    'Payment-related actions are processed through integrated billing providers rather than stored manually in spreadsheets.',
    'Access is limited by role so owners, admins, and tenants only see the data that belongs to them.',
];

export default function CoreUiApp() {
    return (
        <div className="marketing-page">
            <header className="marketing-nav">
                <a className="marketing-brand" href="/">
                    <span className="marketing-brand__mark">N</span>
                    <span>Noma</span>
                </a>

                <nav className="marketing-nav__links" aria-label="Primary navigation">
                    <a href="#product">Product</a>
                    <a href="#pricing">Pricing</a>
                    <a href="#contact">Contact</a>
                    <a href="#privacy">Privacy</a>
                </nav>

                <a className="marketing-nav__login" href="/login">
                    Login
                </a>
            </header>

            <main>
                <section className="hero-section">
                    <div className="hero-copy">
                        <div className="hero-copy__eyebrow">Rental operations, without the spreadsheet overhead</div>
                        <h1>Manage properties, billing, and tenant communication in one focused system.</h1>
                        <p>
                            Noma is a rental management SaaS for owners and property managers who need a clear way to
                            run leases, invoices, meter readings, and account access without building a custom back
                            office.
                        </p>

                        <div className="hero-actions">
                            <a className="button button--primary" href="#pricing">
                                See pricing
                            </a>
                            <a className="button button--secondary" href="/login">
                                Login to dashboard
                            </a>
                        </div>

                        <div className="hero-meta">
                            <span>Property portfolios</span>
                            <span>Recurring invoicing</span>
                            <span>Tenant self-service</span>
                        </div>
                    </div>

                    <div className="hero-panel" aria-label="Product overview">
                        <div className="hero-panel__window">
                            <div className="hero-panel__topbar">
                                <span></span>
                                <span></span>
                                <span></span>
                            </div>

                            <div className="hero-panel__body">
                                <div className="hero-panel__summary">
                                    <div>
                                        <p className="hero-panel__label">This month</p>
                                        <strong>128 invoices issued</strong>
                                    </div>
                                    <span className="hero-panel__status">94% collected</span>
                                </div>

                                <div className="hero-panel__grid">
                                    <article>
                                        <p>Properties</p>
                                        <strong>24</strong>
                                    </article>
                                    <article>
                                        <p>Units</p>
                                        <strong>186</strong>
                                    </article>
                                    <article>
                                        <p>Active leases</p>
                                        <strong>173</strong>
                                    </article>
                                    <article>
                                        <p>Open balances</p>
                                        <strong>EUR 8.4k</strong>
                                    </article>
                                </div>

                                <div className="hero-panel__list">
                                    <div>
                                        <span>Rent</span>
                                        <strong>Automated recurring charge</strong>
                                    </div>
                                    <div>
                                        <span>Utilities</span>
                                        <strong>Tenant-submitted meter readings</strong>
                                    </div>
                                    <div>
                                        <span>Exports</span>
                                        <strong>PDF invoices and accounting files</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="proof-strip" aria-label="Key outcomes">
                    <div>
                        <strong>Leases</strong>
                        <span>Create and manage active rental agreements.</span>
                    </div>
                    <div>
                        <strong>Invoices</strong>
                        <span>Issue, send, print, and reconcile billing.</span>
                    </div>
                    <div>
                        <strong>Meters</strong>
                        <span>Collect readings and convert them into charges.</span>
                    </div>
                    <div>
                        <strong>Reports</strong>
                        <span>Export portfolio and billing data quickly.</span>
                    </div>
                </section>

                <section className="content-section" id="product">
                    <div className="section-heading">
                        <span>What this SaaS does</span>
                        <h2>A practical control layer for rental administration.</h2>
                        <p>
                            The application structure already includes owners, tenants, properties, units, leases,
                            invoices, payments, meter readings, reports, exports, and subscription billing. This page
                            presents that clearly for prospects before they log in.
                        </p>
                    </div>

                    <div className="feature-grid">
                        {features.map((feature) => (
                            <article className="feature-card" key={feature.title}>
                                <p className="feature-card__eyebrow">{feature.eyebrow}</p>
                                <h3>{feature.title}</h3>
                                <p>{feature.copy}</p>
                            </article>
                        ))}
                    </div>
                </section>

                <section className="split-section">
                    <div className="split-section__copy">
                        <span>Why it feels simpler</span>
                        <h2>Clear inputs, clear status, less manual follow-up.</h2>
                        <p>
                            The design goal is not to impress with dashboard noise. It is to make rent operations easy
                            to read: who owes what, which leases are active, which invoices are sent, and what needs
                            action next.
                        </p>
                        <ul>
                            <li>Role-based access for admin, owner, and tenant accounts</li>
                            <li>Subscription and billing flows already built into the platform</li>
                            <li>Export-friendly data model for accounting and reporting tasks</li>
                        </ul>
                    </div>

                    <div className="split-section__panel">
                        <div className="mini-dashboard">
                            <div className="mini-dashboard__card">
                                <span>Outstanding</span>
                                <strong>EUR 8,420</strong>
                                <small>12 invoices need follow-up</small>
                            </div>
                            <div className="mini-dashboard__card">
                                <span>Meter submissions</span>
                                <strong>43 received</strong>
                                <small>7 still pending this cycle</small>
                            </div>
                            <div className="mini-dashboard__timeline">
                                <div>
                                    <span>Mar 01</span>
                                    <strong>Invoices issued</strong>
                                </div>
                                <div>
                                    <span>Mar 05</span>
                                    <strong>Reminder batch sent</strong>
                                </div>
                                <div>
                                    <span>Mar 12</span>
                                    <strong>Export delivered to accounting</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section className="content-section" id="pricing">
                    <div className="section-heading">
                        <span>Pricing</span>
                        <h2>Simple plans that match portfolio size.</h2>
                        <p>
                            These prices are placeholder commercial copy for the public page. Replace them with your
                            live subscription values when the billing offer is final.
                        </p>
                    </div>

                    <div className="pricing-grid">
                        {plans.map((plan) => (
                            <article className={`pricing-card${plan.featured ? ' is-featured' : ''}`} key={plan.name}>
                                <div className="pricing-card__header">
                                    <h3>{plan.name}</h3>
                                    {plan.featured ? <span>Most selected</span> : null}
                                </div>
                                <div className="pricing-card__price">
                                    <strong>{plan.price}</strong>
                                    <small>{plan.period}</small>
                                </div>
                                <p>{plan.description}</p>
                                <ul>
                                    {plan.features.map((feature) => (
                                        <li key={feature}>{feature}</li>
                                    ))}
                                </ul>
                                <a className={`button ${plan.featured ? 'button--primary' : 'button--secondary'}`} href="/login">
                                    {plan.price === 'Custom' ? 'Contact us' : 'Start with login'}
                                </a>
                            </article>
                        ))}
                    </div>
                </section>

                <section className="contact-band" id="contact">
                    <div>
                        <span>Contact us</span>
                        <h2>Need a walkthrough or a custom rollout plan?</h2>
                        <p>
                            For pricing, onboarding, or partnership questions, contact the team directly and we can map
                            the setup to your portfolio structure.
                        </p>
                    </div>

                    <div className="contact-band__panel">
                        <a href="mailto:hello@rent.mbc.lv">hello@rent.mbc.lv</a>
                        <a href="tel:+37120000000">+371 20 000 000</a>
                        <p>Response target: within one business day</p>
                    </div>
                </section>

                <section className="content-section" id="privacy">
                    <div className="section-heading">
                        <span>Privacy policy</span>
                        <h2>Built for business data that should stay structured and controlled.</h2>
                        <p>
                            This short policy block is suitable for a landing page summary. If you need a full legal
                            privacy policy, add a dedicated Blade route later and link it from here.
                        </p>
                    </div>

                    <div className="privacy-card">
                        {privacyItems.map((item) => (
                            <div className="privacy-card__item" key={item}>
                                <span></span>
                                <p>{item}</p>
                            </div>
                        ))}
                    </div>
                </section>
            </main>

            <footer className="marketing-footer">
                <div>
                    <strong>Noma</strong>
                    <p>Rental management SaaS for modern property operations.</p>
                </div>

                <div className="marketing-footer__links">
                    <a href="#product">Product</a>
                    <a href="#pricing">Pricing</a>
                    <a href="#contact">Contact us</a>
                    <a href="#privacy">Privacy policy</a>
                    <a href="/login">Login</a>
                </div>
            </footer>
        </div>
    );
}
