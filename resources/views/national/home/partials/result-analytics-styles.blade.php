<style>
    .result-shell {
        display: grid;
        gap: 1.5rem;
    }

    .result-kpi {
        height: 100%;
        padding: 1.2rem 1.25rem;
        border-radius: 22px;
        background: linear-gradient(180deg, rgba(255, 255, 255, 0.98), rgba(243, 247, 251, 0.98));
        border: 1px solid rgba(11, 39, 71, 0.08);
        box-shadow: 0 18px 40px rgba(14, 30, 61, 0.08);
    }

    .result-kpi__label {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        color: #607089;
        font-size: .8rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .result-kpi__value {
        margin-top: .8rem;
        color: #10233d;
        font-size: clamp(1.65rem, 2vw, 2.25rem);
        font-weight: 800;
        line-height: 1;
    }

    .result-kpi__sub {
        margin-top: .55rem;
        color: #6e7e95;
        font-size: .92rem;
    }

    .result-panel {
        background: #fff;
        border: 1px solid rgba(11, 39, 71, 0.08);
        border-radius: 24px;
        box-shadow: 0 22px 44px rgba(14, 30, 61, 0.08);
        overflow: hidden;
    }

    .result-panel__header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 1rem;
        padding: 1.1rem 1.25rem;
        border-bottom: 1px solid rgba(11, 39, 71, 0.07);
    }

    .result-panel__title {
        margin: 0;
        color: #10233d;
        font-size: 1.02rem;
        font-weight: 800;
    }

    .result-panel__sub {
        color: #6e7e95;
        font-size: .92rem;
    }

    .result-panel__body {
        padding: 1.25rem;
    }

    .result-toolbar {
        display: flex;
        flex-wrap: wrap;
        gap: .85rem;
        align-items: end;
        padding: 1.15rem 1.25rem;
        background: linear-gradient(135deg, rgba(16, 35, 61, 0.04), rgba(15, 109, 95, 0.06));
        border: 1px solid rgba(11, 39, 71, 0.07);
        border-radius: 24px;
    }

    .result-toolbar .form-group {
        margin-bottom: 0;
        min-width: 220px;
        flex: 1 1 220px;
    }

    .result-toolbar label {
        display: block;
        margin-bottom: .4rem;
        color: #43526b;
        font-size: .82rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .05em;
    }

    .result-toolbar .form-control {
        height: 48px;
        border-radius: 14px;
        border-color: rgba(11, 39, 71, 0.12);
        box-shadow: none;
    }

    .result-chip {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        padding: .5rem .8rem;
        border-radius: 999px;
        background: rgba(16, 35, 61, 0.06);
        color: #23354d;
        font-size: .82rem;
        font-weight: 700;
    }

    .insight-list {
        display: grid;
        gap: .9rem;
    }

    .insight-row {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        padding-bottom: .9rem;
        border-bottom: 1px solid rgba(11, 39, 71, 0.07);
    }

    .insight-row:last-child {
        padding-bottom: 0;
        border-bottom: 0;
    }

    .insight-row strong {
        color: #10233d;
        font-size: .96rem;
    }

    .insight-row span {
        color: #6e7e95;
        font-size: .88rem;
    }

    .coverage-track {
        width: 100%;
        height: 8px;
        background: rgba(16, 35, 61, 0.08);
        border-radius: 999px;
        overflow: hidden;
    }

    .coverage-track span {
        display: block;
        height: 100%;
        border-radius: inherit;
        background: linear-gradient(90deg, #0f6d5f, #1d8a79);
    }

    .metric-stack {
        display: grid;
        gap: .25rem;
    }

    .metric-stack strong {
        color: #10233d;
        font-size: .96rem;
    }

    .metric-stack span {
        color: #6e7e95;
        font-size: .84rem;
    }

    .result-data-table tbody td {
        vertical-align: middle;
    }

    .result-empty-state {
        padding: 2rem;
        text-align: center;
        color: #6e7e95;
    }

    .result-summary-grid {
        display: grid;
        gap: .9rem;
    }

    .detail-stat {
        padding: .95rem 1rem;
        border-radius: 18px;
        background: rgba(16, 35, 61, 0.04);
        border: 1px solid rgba(11, 39, 71, 0.06);
    }

    .detail-stat__label {
        color: #607089;
        font-size: .78rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .detail-stat__value {
        margin-top: .4rem;
        color: #10233d;
        font-size: 1.3rem;
        font-weight: 800;
    }

    @media (max-width: 767.98px) {
        .result-toolbar {
            padding: 1rem;
        }

        .result-panel__header,
        .result-panel__body {
            padding: 1rem;
        }
    }
</style>
