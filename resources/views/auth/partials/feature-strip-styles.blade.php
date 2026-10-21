@once
<style>
        .signup-feature-strip {
            font-family: inherit;
            container-type: inline-size;
            border: 1px solid #dfe9e7;
            border-radius: 16px;
            background: rgb(255 255 255 / 85%);
            overflow: hidden;
        }
        .signup-feature-strip-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); padding-block: 14px; }
        .signup-feature-strip-item { display: grid; grid-template-columns: 14px minmax(0, 1fr); grid-template-rows: minmax(32px, auto) auto; align-content: start; min-width: 0; padding-inline: 14px; column-gap: 7px; row-gap: 5px; }
        .signup-feature-strip-item + .signup-feature-strip-item { border-left: 1px solid #dfe9e7; }
        .signup-feature-strip-title { grid-column: 2; grid-row: 1; color: #2D7080; font-weight: 600; letter-spacing: .045em; line-height: 1.45; overflow-wrap: break-word; }
        .signup-feature-strip-value { grid-column: 2; grid-row: 2; color: #0B2638; line-height: 1.5; overflow-wrap: break-word; }
        .signup-feature-strip-icon { display: block; grid-column: 1; grid-row: 1; width: 14px; height: 14px; margin-top: 1px; color: #2D7080; }
        @container (max-width: 320px) {
            .signup-feature-strip-grid { grid-template-columns: minmax(0, 1fr); padding-block: 0; }
            .signup-feature-strip-item { grid-template-columns: 14px minmax(0, 1fr) auto; grid-template-rows: auto; align-items: center; padding: 12px 14px; column-gap: 8px; row-gap: 0; }
            .signup-feature-strip-icon { margin-top: 0; }
            .signup-feature-strip-value { grid-column: 3; grid-row: 1; }
            .signup-feature-strip-item + .signup-feature-strip-item { border-left: 0; border-top: 1px solid #dfe9e7; }
        }
</style>
@endonce
