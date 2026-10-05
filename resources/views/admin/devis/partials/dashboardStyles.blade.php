<style>
    .devis-dashboard-page main {
        min-width: 0;
    }

    .devis-dashboard-page .devis-form :is(input:not([type="checkbox"]):not([type="radio"]), select, textarea),
    .devis-dashboard-page .devis-filter-control {
        width: 100%;
        max-width: 100%;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background-color: #f8fafc;
        color: #1e293b;
        font-size: 13px;
        padding: 8px 12px;
        outline: none;
        transition: border-color .15s, background-color .15s, box-shadow .15s;
    }

    .devis-dashboard-page .devis-form :is(input:not([type="checkbox"]):not([type="radio"]), select, textarea):focus,
    .devis-dashboard-page .devis-filter-control:focus {
        border-color: #2D7080;
        background-color: #fff;
        box-shadow: 0 0 0 3px rgba(45, 112, 128, .12);
    }

    .devis-dashboard-page input[type="checkbox"],
    .devis-dashboard-page input[type="radio"] {
        accent-color: #2D7080;
    }

    .devis-dashboard-page .devis-form :is(input[readonly], input:disabled, select:disabled) {
        background-color: #f1f5f9;
        color: #64748b;
    }

    .devis-dashboard-page .devis-form label {
        display: block;
        margin-bottom: 5px;
        color: #475569;
        font-size: 11.5px;
        font-weight: 600;
    }

    .devis-dashboard-page .devis-form .detail-row button[onclick="deleteDetailRow(this)"] {
        width: 32px;
        height: 32px;
        margin-bottom: 0 !important;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: #fff !important;
        color: #dc2626 !important;
        padding: 0;
        box-shadow: none;
    }

    .devis-dashboard-page .devis-form .detail-row button[onclick="deleteDetailRow(this)"]:hover {
        border-color: #fecaca;
        background: #fff1f2 !important;
    }

    .devis-dashboard-page .devis-form .detail-row button[onclick="deleteDetailRow(this)"] svg {
        width: 15px;
        height: 15px;
    }

    .devis-dashboard-page #devisTable th {
        padding: 10px 12px;
        border-bottom: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        font-weight: 700;
        text-align: left;
        text-transform: uppercase;
        white-space: nowrap;
    }

    .devis-dashboard-page #devisTable td {
        padding: 9px 12px;
        border-bottom: 1px solid #f1f5f9;
        color: #475569;
        font-size: 12.5px;
        vertical-align: middle;
    }

    .devis-dashboard-page #devisTable tbody tr:hover td {
        background-color: #f8fafc;
    }

    .devis-dashboard-page .devis-action-row {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-start;
        gap: 6px;
    }

    .devis-dashboard-page .devis-action {
        display: inline-flex;
        min-height: 30px;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: 1px solid transparent;
        border-radius: 6px;
        padding: 5px 9px;
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1.2;
        text-decoration: none;
        white-space: nowrap;
        transition: background-color .15s, border-color .15s, color .15s;
    }

    .devis-dashboard-page .devis-action svg {
        width: 13px;
        height: 13px;
        flex: none;
    }

    .devis-dashboard-page .devis-action-primary {
        border-color: #2D7080;
        background: #2D7080;
        color: #fff;
    }

    .devis-dashboard-page .devis-action-primary:hover {
        background: #245d69;
    }

    .devis-dashboard-page .devis-action-neutral {
        border-color: #cbd5e1;
        background: #fff;
        color: #475569;
    }

    .devis-dashboard-page .devis-action-neutral:hover {
        background: #f8fafc;
    }

    .devis-dashboard-page .devis-action-success {
        border-color: #86efac;
        background: #f0fdf4;
        color: #15803d;
    }

    .devis-dashboard-page .devis-action-danger {
        border-color: #fca5a5;
        background: #fff1f2;
        color: #b91c1c;
    }

    .devis-dashboard-page .devis-action-muted {
        border-color: #cbd5e1;
        background: #f8fafc;
        color: #475569;
    }

    .devis-dashboard-page #devisTable tbody td:nth-last-child(2) .devis-action-row button:not(.devis-action) {
        display: inline-flex;
        min-height: 30px;
        align-items: center;
        justify-content: center;
        gap: 6px;
        border: 1px solid #86efac;
        border-radius: 6px;
        background: #f0fdf4;
        color: #15803d;
        padding: 5px 9px;
        font-size: 11.5px;
        font-weight: 600;
        line-height: 1.2;
        white-space: nowrap;
    }

    .devis-dashboard-page #devisTable tbody td:nth-last-child(2) .devis-action-row button[class*="bg-red-500"] {
        border-color: #fca5a5;
        background: #fff1f2;
        color: #b91c1c;
    }

    .devis-dashboard-page #devisTable tbody td:nth-last-child(2) .devis-action-row button::before {
        width: 13px;
        height: 13px;
        flex: none;
        background: currentColor;
        content: "";
        -webkit-mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='black' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M5 13l4 4L19 7'/%3E%3C/svg%3E") center / contain no-repeat;
        mask: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='black' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M5 13l4 4L19 7'/%3E%3C/svg%3E") center / contain no-repeat;
    }

    .devis-dashboard-page #devisTable tbody td:nth-last-child(2) .devis-action-row button[class*="bg-red-500"]::before {
        -webkit-mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='black' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M6 18L18 6M6 6l12 12'/%3E%3C/svg%3E");
        mask-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='black' stroke-width='2.5'%3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M6 18L18 6M6 6l12 12'/%3E%3C/svg%3E");
    }

    .devis-dashboard-page #devisTable td:last-child .devis-action-row > form {
        width: auto;
        max-width: 100%;
    }

    .devis-dashboard-page #devisTable td:last-child .devis-action-row > form button,
    .devis-dashboard-page #devisTable td:last-child .devis-action-row > a {
        max-width: 100%;
    }

    .devis-dashboard-page #devisReportModal > div,
    .devis-dashboard-page #sendDevisModal > div {
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 20px 50px rgba(11, 38, 56, .16);
    }

    .devis-dashboard-page #saveButton.bg-blue-600 {
        background: #0B2638;
    }

    .devis-dashboard-page #saveButton.bg-blue-600:hover {
        background: #14364d;
    }

    .devis-dashboard-page .devis-link-normal {
        color: #2D7080;
        font-weight: 600;
    }

    .devis-dashboard-page .devis-link-normal:hover {
        color: #0B2638;
        text-decoration: underline;
    }

    .devis-dashboard-page #devisTable td a.text-blue-600 {
        color: #2D7080;
        font-weight: 600;
    }

    .devis-dashboard-page #devisTable td a.text-blue-600:hover {
        color: #0B2638;
        text-decoration: underline;
    }

    .devis-dashboard-page #ClientDropdown {
        border-color: #e2e8f0;
        border-radius: 8px;
        background: #f8fafc;
    }

    .devis-dashboard-page #ClientDropdown:focus-visible {
        border-color: #2D7080;
        outline: 2px solid rgba(45, 112, 128, .2);
        outline-offset: 1px;
    }

    @media (max-width: 1024px) {
        .devis-dashboard-page .table-responsive {
            overflow-x: visible;
        }

        .devis-dashboard-page #devisTable tbody tr.devis-row {
            margin: 0 12px 12px;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(11, 38, 56, .06);
        }

        .devis-dashboard-page #devisTable tbody tr.devis-row td {
            border-color: #f1f5f9;
            border-radius: 6px;
            background: #fff;
            word-break: break-word;
        }

        .devis-dashboard-page #devisTable tbody tr.devis-row td[data-label]::before {
            color: #94a3b8;
            font-size: 10px;
        }
    }
</style>