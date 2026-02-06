(globalThis.webpackChunkformgent=globalThis.webpackChunkformgent||[]).push([[121],{2736(M,N,e){M.exports=e(16186)()},16186(M,N,e){"use strict";var t=e(62985);function g(){}function n(){}n.resetWarningCache=g,M.exports=function(){function M(M,N,e,g,n,D){if(D!==t){var i=new Error("Calling PropTypes validators directly is not supported by the `prop-types` package. Use PropTypes.checkPropTypes() to call them. Read more at http://fb.me/use-check-prop-types");throw i.name="Invariant Violation",i}}function N(){return M}M.isRequired=M;var e={array:M,bigint:M,bool:M,func:M,number:M,object:M,string:M,symbol:M,any:M,arrayOf:N,element:M,elementType:M,instanceOf:N,node:M,objectOf:N,oneOf:N,oneOfType:N,shape:N,exact:N,checkPropTypes:n,resetWarningCache:g};return e.PropTypes=e,e}},29119(M,N,e){"use strict";e.d(N,{A:()=>T});var t=e(56427),g=e(14356),n=e(13560),D=e(27723),i=e(10790);function T(M){const{children:N,title:e,className:T,AlertContent:j,onCancel:a,onFooterCancel:c,onSubmit:l,hasCancelButton:I,hasSubmitButton:o,isDisableAction:U,submitText:d,cancelText:z,cancelBtnType:s,submitBtnType:r="danger",isDismissible:x}=M;return(0,i.jsx)(t.Modal,{title:e,overlayClassName:`formgent-modal formgent-modal-default ${T}`,onRequestClose:a,isDismissible:x,children:(0,i.jsxs)(n.jN,{className:`${T}__modal`,children:[N,!U&&(0,i.jsxs)("div",{className:`formgent-modal-action ${T}-action`,children:[I&&(0,i.jsx)(g.AntButton,{ghost:!0,className:"formgent-modal-cancel-btn",onClick:M=>{M.preventDefault(),c?c():a()},children:z||(0,D.__)("Cancel","formgent")}),o&&(0,i.jsx)(g.AntButton,{type:"primary",onClick:M=>{M.preventDefault(),l()},danger:!0,children:d})]})]})})}},53007(M,N,e){"use strict";e.d(N,{A:()=>t}),e(51609);const t="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTYiIGhlaWdodD0iMTYiIHZpZXdCb3g9IjAgMCAxNiAxNiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggaWQ9ImZvcm1nZW50LXBheW1lbnQtcGF0aCIgZD0iTTEgNC40NTQ1NUMxIDMuODAzNTYgMS4yNTM5OSAzLjE3OTI0IDEuNzA2MDggMi43MTg5MkMyLjE1ODE4IDIuMjU4NiAyLjc3MTM1IDIgMy40MTA3MSAySDEzLjU4OTNDMTQuMjI4NiAyIDE0Ljg0MTggMi4yNTg2IDE1LjI5MzkgMi43MTg5MkMxNS43NDYgMy4xNzkyNCAxNiAzLjgwMzU2IDE2IDQuNDU0NTVWMTEuNTQ1NUMxNiAxMi4xOTY0IDE1Ljc0NiAxMi44MjA4IDE1LjI5MzkgMTMuMjgxMUMxNC44NDE4IDEzLjc0MTQgMTQuMjI4NiAxNCAxMy41ODkzIDE0SDMuNDEwNzFDMi43NzEzNSAxNCAyLjE1ODE4IDEzLjc0MTQgMS43MDYwOCAxMy4yODExQzEuMjUzOTkgMTIuODIwOCAxIDEyLjE5NjQgMSAxMS41NDU1VjQuNDU0NTVaTTMuNDEwNzEgMy4wOTA5MUMzLjA1NTUxIDMuMDkwOTEgMi43MTQ4NiAzLjIzNDU4IDIuNDYzNyAzLjQ5MDMxQzIuMjEyNTMgMy43NDYwNCAyLjA3MTQzIDQuMDkyODkgMi4wNzE0MyA0LjQ1NDU1VjUuMjcyNzNIMTQuOTI4NlY0LjQ1NDU1QzE0LjkyODYgNC4wOTI4OSAxNC43ODc1IDMuNzQ2MDQgMTQuNTM2MyAzLjQ5MDMxQzE0LjI4NTEgMy4yMzQ1OCAxMy45NDQ1IDMuMDkwOTEgMTMuNTg5MyAzLjA5MDkxSDMuNDEwNzFaTTIuMDcxNDMgMTEuNTQ1NUMyLjA3MTQzIDExLjkwNzEgMi4yMTI1MyAxMi4yNTQgMi40NjM3IDEyLjUwOTdDMi43MTQ4NiAxMi43NjU0IDMuMDU1NTEgMTIuOTA5MSAzLjQxMDcxIDEyLjkwOTFIMTMuNTg5M0MxMy45NDQ1IDEyLjkwOTEgMTQuMjg1MSAxMi43NjU0IDE0LjUzNjMgMTIuNTA5N0MxNC43ODc1IDEyLjI1NCAxNC45Mjg2IDExLjkwNzEgMTQuOTI4NiAxMS41NDU1VjYuMzYzNjRIMi4wNzE0M1YxMS41NDU1Wk0xMS4xNzg2IDkuNjM2MzZIMTIuNzg1N0MxMi45Mjc4IDkuNjM2MzYgMTMuMDY0MSA5LjY5MzgzIDEzLjE2NDUgOS43OTYxMkMxMy4yNjUgOS44OTg0MiAxMy4zMjE0IDEwLjAzNzIgMTMuMzIxNCAxMC4xODE4QzEzLjMyMTQgMTAuMzI2NSAxMy4yNjUgMTAuNDY1MiAxMy4xNjQ1IDEwLjU2NzVDMTMuMDY0MSAxMC42Njk4IDEyLjkyNzggMTAuNzI3MyAxMi43ODU3IDEwLjcyNzNIMTEuMTc4NkMxMS4wMzY1IDEwLjcyNzMgMTAuOTAwMiAxMC42Njk4IDEwLjc5OTggMTAuNTY3NUMxMC42OTkzIDEwLjQ2NTIgMTAuNjQyOSAxMC4zMjY1IDEwLjY0MjkgMTAuMTgxOEMxMC42NDI5IDEwLjAzNzIgMTAuNjk5MyA5Ljg5ODQyIDEwLjc5OTggOS43OTYxMkMxMC45MDAyIDkuNjkzODMgMTEuMDM2NSA5LjYzNjM2IDExLjE3ODYgOS42MzYzNloiIGZpbGw9IiM1RTUzRjkiLz4KPC9zdmc+Cg=="},61121(M,N,e){"use strict";e.r(N),e.d(N,{default:()=>IM});var t=e(56427),g=e(47143),n=e(52619),D=e(27723),i=e(92279),T=e(70121),j=e(53996);e(51609);var a=e(53007),c=e(86087),l=e(93832),I=e(14356),o=e(29119);const U="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgdmlld0JveD0iMCAwIDIwIDIwIiBmaWxsPSJub25lIj4KICA8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTIuNSAxMEMyLjUgOS4wNzk1MiAzLjI0NjE5IDguMzMzMzMgNC4xNjY2NyA4LjMzMzMzQzUuMDg3MTQgOC4zMzMzMyA1LjgzMzMzIDkuMDc5NTIgNS44MzMzMyAxMEM1LjgzMzMzIDEwLjkyMDUgNS4wODcxNCAxMS42NjY3IDQuMTY2NjcgMTEuNjY2N0MzLjI0NjE5IDExLjY2NjcgMi41IDEwLjkyMDUgMi41IDEwWk04LjMzMzMzIDEwQzguMzMzMzMgOS4wNzk1MiA5LjA3OTUzIDguMzMzMzMgMTAgOC4zMzMzM0MxMC45MjA1IDguMzMzMzMgMTEuNjY2NyA5LjA3OTUyIDExLjY2NjcgMTBDMTEuNjY2NyAxMC45MjA1IDEwLjkyMDUgMTEuNjY2NyAxMCAxMS42NjY3QzkuMDc5NTMgMTEuNjY2NyA4LjMzMzMzIDEwLjkyMDUgOC4zMzMzMyAxMFpNMTQuMTY2NyAxMEMxNC4xNjY3IDkuMDc5NTIgMTQuOTEyOSA4LjMzMzMzIDE1LjgzMzMgOC4zMzMzM0MxNi43NTM4IDguMzMzMzMgMTcuNSA5LjA3OTUyIDE3LjUgMTBDMTcuNSAxMC45MjA1IDE2Ljc1MzggMTEuNjY2NyAxNS44MzMzIDExLjY2NjdDMTQuOTEyOSAxMS42NjY3IDE0LjE2NjcgMTAuOTIwNSAxNC4xNjY3IDEwWiIgZmlsbD0iIzc0N0M4OSIvPgo8L3N2Zz4=",d="data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNyIgdmlld0JveD0iMCAwIDE2IDE3IiBmaWxsPSJub25lIiBjbGFzcz0iZm9ybWdlbnQtc3ZnLXN0cm9rZS1vbmx5Ij4KICA8ZyBjbGlwLXBhdGg9InVybCgjY2xpcDBfNzQ3MV8xMDAyKSI+CiAgICA8cGF0aCBkPSJNNS4zMzM5OCAxMC44Mjc0TDEwLjY2NjMgNS40OTQxMyIgc3Ryb2tlPSIjMTQxOTIxIiBzdHJva2Utd2lkdGg9IjEuNSIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgogICAgPHBhdGggZD0iTTEwLjA4NCA3LjkxMTExTDExLjAwMDcgOC44Mjc3OEwxNC42NjczIDUuMTYxMTFMMTEuMDAwNyAxLjQ5NDQ1TDcuMzMzOTggNS4xNjExMUw4LjI1MDY1IDYuMDc3NzgiIHN0cm9rZT0iIzE0MTkyMSIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KICAgIDxwYXRoIGQ9Ik03Ljc1MDY1IDEwLjI0NDRMOC42NjczMiAxMS4xNjExTDUuMDAwNjUgMTQuODI3OEwxLjMzMzk4IDExLjE2MTFMNS4wMDA2NSA3LjQ5NDQ1TDUuOTE3MzIgOC40MTExMSIgc3Ryb2tlPSIjMTQxOTIxIiBzdHJva2Utd2lkdGg9IjEuNSIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgogICAgPHBhdGggZD0iTTE0LjAwMTMgMTAuODI3OUgxMi4wMDEzTTEwLjY2OCAxNC4xNjEzVjEyLjE2MTMiIHN0cm9rZT0iIzE0MTkyMSIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KICAgIDxwYXRoIGQ9Ik0yIDUuNDk0MjhINE01LjMzMzMzIDIuMTYwOTVWNC4xNjA5NSIgc3Ryb2tlPSIjMTQxOTIxIiBzdHJva2Utd2lkdGg9IjEuNSIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgogIDwvZz4KICA8ZGVmcz4KICAgIDxjbGlwUGF0aCBpZD0iY2xpcDBfNzQ3MV8xMDAyIj4KICAgICAgPHJlY3Qgd2lkdGg9IjE2IiBoZWlnaHQ9IjE2IiBmaWxsPSJ3aGl0ZSIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoMCAwLjE2MDk1KSIvPgogICAgPC9jbGlwUGF0aD4KICA8L2RlZnM+Cjwvc3ZnPg==";var z=e(29491),s=e(78739),r=e(74461);const x=r.Ay.div`
    width: 100%;
    max-width: 740px;
    margin-top: 20px;
    .formgent-collapse__content{
        margin: 0 8px 8px;
        padding-top: 24px;
        border-top: 1px solid var(--formgent-color-gray-200);
    }
    .formgent-zapier-form-block{
        &:not(:last-child){
            margin-bottom: 30px;
        }
        .formgent-zapier-form-block__title{
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 6px;
            color: var(--formgent-color-gray);
        }
        .formgent-zapier-form-block__subtitle{
            font-size: 14px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 18px;
            color: var(--formgent-color-text);
        }
    }
    .formgent-zapier-form-field{
        &:not(:last-child){
            margin-bottom: 24px;
        }
        > span{
            color: var(--formgent-color-dark);
            font-size: 14px;
            font-weight: 600;
            letter-spacing: -0.15px;
            display: block;
            margin-bottom: 8px;
        }
        .ant-input-affix-wrapper,
        input.formgent-form-field{
            background: none;
            border-radius: 8px;
            border: 1px solid var(--formgent-color-gray-300);
            box-shadow: 0px 1px 2px 0px rgba(16, 24, 40, 0.05);
            padding: 4px 14px;
            transition: none;
            .ant-input{
                min-height: 38px;
                box-shadow: none;
            }
            &[disabled]{
                background-color: var(--formgent-color-gray-100);
            }
        }
        input.formgent-form-field{
            padding: 8px 14px;
        }
        .formgent-zapier-form-field-input{
            width: 100%;
            display: flex;
            align-items: center;
            gap: 8px;
            .formgent-ant-input{
                flex: 1;
            }
            .formgent-ant-button-wrapper{
                flex: none;
            }
            .formgent-ant-select{
                width: 100%;
                .ant-select{
                    width: 100%;
                }
            }
        }
        a{
            color: var(--formgent-color-info);
            font-size: 14px;
            font-weight: 500;
            letter-spacing: -0.15px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            margin-top: 8px;
            svg path{
                fill: var( --formgent-color-info );
            }
        }
        .formgent-form-field-copy{
            width: 48px;
            height: 48px;
            padding: 0;
            svg{
                width: 18px;
                height: 18px;
                path{
                    stroke: var(--formgent-color-gray-600);
                }
            }
        }
    }
`,u=r.Ay.div`
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    .formgent-connected-settings__title{
        font-size: 20px;
        font-weight: 600;
        color: var(--formgent-color-gray-800);
    }
    .formgent-connected-settings__text{
        font-size: 14px;
        font-weight: 400;
        margin: 8px 0 0;
        color: var(--formgent-color-gray-600);
    }
    .formgent-connected-settings__content{
        text-align: center;
        margin: 20px 0 16px;
    }
`,y="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTAwJSIgaGVpZ2h0PSIxMDAlIiB2aWV3Qm94PSIwIDAgMjQgMjQiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CiA8cGF0aCBkPSJNMTYgOFY1LjJDMTYgNC4wNzk5IDE2IDMuNTE5ODQgMTUuNzgyIDMuMDkyMDJDMTUuNTkwMyAyLjcxNTY5IDE1LjI4NDMgMi40MDk3MyAxNC45MDggMi4yMTc5OUMxNC40ODAyIDIgMTMuOTIwMSAyIDEyLjggMkg1LjJDNC4wNzk5IDIgMy41MTk4NCAyIDMuMDkyMDIgMi4yMTc5OUMyLjcxNTY5IDIuNDA5NzMgMi40MDk3MyAyLjcxNTY5IDIuMjE3OTkgMy4wOTIwMkMyIDMuNTE5ODQgMiA0LjA3OTkgMiA1LjJWMTIuOEMyIDEzLjkyMDEgMiAxNC40ODAyIDIuMjE3OTkgMTQuOTA4QzIuNDA5NzMgMTUuMjg0MyAyLjcxNTY5IDE1LjU5MDMgMy4wOTIwMiAxNS43ODJDMy41MTk4NCAxNiA0LjA3OTkgMTYgNS4yIDE2SDhNMTEuMiAyMkgxOC44QzE5LjkyMDEgMjIgMjAuNDgwMiAyMiAyMC45MDggMjEuNzgyQzIxLjI4NDMgMjEuNTkwMyAyMS41OTAzIDIxLjI4NDMgMjEuNzgyIDIwLjkwOEMyMiAyMC40ODAyIDIyIDE5LjkyMDEgMjIgMTguOFYxMS4yQzIyIDEwLjA3OTkgMjIgOS41MTk4NCAyMS43ODIgOS4wOTIwMkMyMS41OTAzIDguNzE1NjkgMjEuMjg0MyA4LjQwOTczIDIwLjkwOCA4LjIxNzk5QzIwLjQ4MDIgOCAxOS45MjAxIDggMTguOCA4SDExLjJDMTAuMDc5OSA4IDkuNTE5ODQgOCA5LjA5MjAyIDguMjE3OTlDOC43MTU2OSA4LjQwOTczIDguNDA5NzMgOC43MTU2OSA4LjIxNzk5IDkuMDkyMDJDOCA5LjUxOTg0IDggMTAuMDc5OSA4IDExLjJWMTguOEM4IDE5LjkyMDEgOCAyMC40ODAyIDguMjE3OTkgMjAuOTA4QzguNDA5NzMgMjEuMjg0MyA4LjcxNTY5IDIxLjU5MDMgOS4wOTIwMiAyMS43ODJDOS41MTk4NCAyMiAxMC4wNzk5IDIyIDExLjIgMjJaIiBzdHJva2U9ImN1cnJlbnRDb2xvciIgc3Ryb2tlLXdpZHRoPSIyIiBzdHJva2UtbGluZWNhcD0icm91bmQiIHN0cm9rZS1saW5lam9pbj0icm91bmQiLz4KIDwvc3ZnPg==";var Q=e(67486),E=e(10790);function m(){const{updateZapierStatus:M,updateSettings:N}=(0,g.dispatch)(T.A.GLOBAL_SETTINGS),{zapierStatus:e,zapierToken:t}=(0,g.useSelect)(M=>{const{getZapierStatus:N,getZapierToken:e}=M(T.A.GLOBAL_SETTINGS);return{zapierStatus:N(),zapierToken:e()}},[]),i=()=>{const M=formgent_data.zapier_endpoint,N=(0,z.useCopyToClipboard)(t,()=>{(0,n.doAction)("formgent-toast",{message:(0,D.__)("Authorization URL copied","formgent"),type:"success"})}),e=(0,z.useCopyToClipboard)(M,()=>{(0,n.doAction)("formgent-toast",{message:(0,D.__)("Secret key copied","formgent"),type:"success"})});return(0,E.jsxs)("div",{className:"formgent-zapier-form-fields",children:[(0,E.jsxs)("div",{className:"formgent-zapier-form-field",children:[(0,E.jsx)("span",{children:(0,D.__)("Secret Key","formgent")}),(0,E.jsxs)("div",{className:"formgent-zapier-form-field-input",children:[(0,E.jsx)(I.AntInputPassword,{className:"formgent-form-field",value:t,readOnly:!0,onChange:()=>{}}),(0,E.jsx)("span",{ref:N,children:(0,E.jsx)(I.AntButton,{className:"formgent-form-field-copy",children:(0,E.jsx)(s.A,{src:y})})})]}),(0,E.jsxs)("a",{href:"https://formgent.com/docs/connect-formgent-with-zapier/",target:"_blank",children:[(0,D.__)("Learn how to connect","formgent"),(0,E.jsx)(Q.A,{})]})]}),(0,E.jsxs)("div",{className:"formgent-zapier-form-field",children:[(0,E.jsx)("span",{children:(0,D.__)("Authorization URL","formgent")}),(0,E.jsxs)("div",{className:"formgent-zapier-form-field-input",children:[(0,E.jsx)(I.AntInput,{className:"formgent-form-field",value:M,readOnly:!0,onChange:()=>{}}),(0,E.jsx)("span",{ref:e,children:(0,E.jsx)(I.AntButton,{className:"formgent-form-field-copy",children:(0,E.jsx)(s.A,{src:y})})})]})]})]})};return(0,E.jsx)(x,{className:"formgent-zapier-integration",children:(0,E.jsx)(I.Collapse,{icon:"data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDIiIGhlaWdodD0iNDIiIHZpZXdCb3g9IjAgMCA0MiA0MiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGcgaWQ9InphcGllciI+CjxwYXRoIGlkPSJ6YXBpZXJfMiIgZD0iTTI0Ljg3ODYgMjQuODg3NUMyMi4zODUzIDI1LjgyNTcgMTkuNTkxNCAyNS44Mjc0IDE3LjA5OCAyNC44ODgxQzE2LjE1ODcgMjIuMzk3NSAxNi4xNTg0IDE5LjYwNDEgMTcuMDk2NiAxNy4xMTMxQzE5LjU4OTMgMTYuMTczMyAyMi4zODYgMTYuMTczMSAyNC44Nzg3IDE3LjExM0MyNS44MTc4IDE5LjYwMzIgMjUuODE3NCAyMi4zOTcyIDI0Ljg3ODYgMjQuODg3NVpNMzkuMTE5NSAxNy45Mzc2SDI4LjM4NjhMMzUuOTc1NSAxMC4zNTMxQzM0Ljc4NDYgOC42ODEyMSAzMy4zMTQyIDcuMjEyNTIgMzEuNjQxMiA2LjAyMjI0TDI0LjA1MTkgMTMuNjA2N1YyLjg4MDg0QzIyLjAyNjQgMi41NDAzMiAxOS45NDkxIDIuNTQwODQgMTcuOTIzNiAyLjg4MDg0VjEzLjYwNjdMMTAuMzM0MiA2LjAyMjI0QzguNjYxNyA3LjIxMTcxIDcuMTkwODkgOC42ODI0MyA1Ljk5OTgyIDEwLjM1MzFMMTMuNTg5NyAxNy45Mzc2SDIuODU3MDlDMi41OTIxNiAyMC4wMjI1IDIuNTA2MDkgMjEuOTgxOSAyLjg1NzA5IDI0LjA2MjVIMTMuNTg5OUw1Ljk5OTk2IDMxLjY0NjlDNy4xOTM5MSAzMy4zMjEzIDguNjU4NzQgMzQuNzg1MiAxMC4zMzQyIDM1Ljk3ODRMMTcuOTIzNiAyOC4zOTM0VjM5LjExOThDMTkuOTQ5MyAzOS40NTkgMjIuMDI2IDM5LjQ1OTIgMjQuMDUxNyAzOS4xMTk4VjI4LjM5MzRMMzEuNjQxOCAzNS45Nzg0QzMzLjMxNTUgMzQuNzg2OSAzNC43ODMzIDMzLjMxOTQgMzUuOTc1NSAzMS42NDY5TDI4LjM4NTYgMjQuMDYyNUgzOS4xMTk1QzM5LjQ2MDIgMjIuMDQgMzkuNDYwMiAxOS45NiAzOS4xMTk1IDE3LjkzNzZaIiBmaWxsPSIjRkY0QTAwIi8+CjwvZz4KPC9zdmc+Cg==",title:(0,D.__)("Zapier","formgent"),subtitle:(0,D.__)("Integrate Zapier with FormGent","formgent"),showToggle:!0,switchChecked:"1"===e,onToggleChange:e=>async function(e){M(!e);try{await N({zapier_status:e?"1":"0"});const M=e?(0,D.__)("Zapier is enabled","formgent"):(0,D.__)("Zapier is disabled","formgent");(0,n.doAction)("formgent-toast",{message:M,type:"success"})}catch(M){console.error(M)}}(e),switchLoading:!1,content:(0,E.jsx)(i,{})})})}function A({handleUpdateZohoSettings:M,handleUpdateSettings:N}){const{settings:e}=(0,g.useSelect)(M=>{const{getSettings:N}=M(T.A.GLOBAL_SETTINGS);return{settings:N()}},[]),{updateSettings:t}=(0,g.dispatch)(T.A.GLOBAL_SETTINGS),i=[{id:"01",label:"US",value:"https://accounts.zoho.com"},{id:"02",label:"AU",value:"https://accounts.zoho.com.au"},{id:"03",label:"EU",value:"https://accounts.zoho.eu"},{id:"04",label:"IN",value:"https://accounts.zoho.in"},{id:"04",label:"CN",value:"https://accounts.zoho.com.cn"}],j=(0,z.useCopyToClipboard)(formgent_data?.home_url||"",()=>{(0,n.doAction)("formgent-toast",{message:(0,D.__)("Homepage URL copied","formgent"),type:"success"})}),a=(0,z.useCopyToClipboard)(`${formgent_data?.admin_url}?fg_zohocrm_confirm=1`,()=>{(0,n.doAction)("formgent-toast",{message:(0,D.__)("Authorized Redirect URL","formgent"),type:"success"})});return(0,c.useEffect)(()=>{e?.zoho?.data_center||M(i[0].value,"data_center")},[]),"1"===e?.zoho?.connected?(0,E.jsxs)(u,{classNam:"formgent-connected-settings",children:[(0,E.jsx)("div",{className:"formgent-connected-settings__icon",children:(0,E.jsx)(s.A,{src:"data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNTEiIGhlaWdodD0iNTEiIHZpZXdCb3g9IjAgMCA1MSA1MSIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggb3BhY2l0eT0iMC40IiBkPSJNMjUuNTMgNTAuMDZDMzkuMzUzNyA1MC4wNiA1MC41NiAzOC44NTM3IDUwLjU2IDI1LjAzQzUwLjU2IDExLjIwNjMgMzkuMzUzNyAwIDI1LjUzIDBDMTEuNzA2MyAwIDAuNSAxMS4yMDYzIDAuNSAyNS4wM0MwLjUgMzguODUzNyAxMS43MDYzIDUwLjA2IDI1LjUzIDUwLjA2WiIgZmlsbD0iIzBDQkM4QiIvPgo8cGF0aCBkPSJNMjUuNTI4MiA0Mi41MzY5QzM1LjE5NTkgNDIuNTM2OSA0My4wMzMgMzQuNjk5OCA0My4wMzMgMjUuMDMyMUM0My4wMzMgMTUuMzY0NSAzNS4xOTU5IDcuNTI3MzQgMjUuNTI4MiA3LjUyNzM0QzE1Ljg2MDYgNy41MjczNCA4LjAyMzQ0IDE1LjM2NDUgOC4wMjM0NCAyNS4wMzIxQzguMDIzNDQgMzQuNjk5OCAxNS44NjA2IDQyLjUzNjkgMjUuNTI4MiA0Mi41MzY5WiIgZmlsbD0iIzBDQkM4QiIvPgo8cGF0aCBkPSJNMjEuOTQ5MiAzMi41MzIyQzIxLjgxMTcgMzIuNTMyMyAyMS42NzU3IDMyLjUwNDUgMjEuNTQ5MyAzMi40NTA2QzIxLjQyMjkgMzIuMzk2NyAyMS4zMDg3IDMyLjMxNzcgMjEuMjEzNiAzMi4yMTg0TDE1LjY0MiAyNi40MTk5QzE1LjU0NzUgMjYuMzIzNyAxNS40NzMgMjYuMjA5NyAxNS40MjI4IDI2LjA4NDVDMTUuMzcyNiAyNS45NTkzIDE1LjM0NzggMjUuODI1MyAxNS4zNDk3IDI1LjY5MDVDMTUuMzUxNyAyNS41NTU2IDE1LjM4MDMgMjUuNDIyNSAxNS40MzQxIDI1LjI5ODhDMTUuNDg3OCAyNS4xNzUgMTUuNTY1NiAyNS4wNjMyIDE1LjY2MjggMjQuOTY5OEMxNS43NjAxIDI0Ljg3NjMgMTUuODc0OSAyNC44MDMxIDE2LjAwMDYgMjQuNzU0M0MxNi4xMjY0IDI0LjcwNTUgMTYuMjYwNiAyNC42ODIyIDE2LjM5NTQgMjQuNjg1NkMxNi41MzAzIDI0LjY4OSAxNi42NjMxIDI0LjcxOTIgMTYuNzg2MiAyNC43NzQzQzE2LjkwOTMgMjQuODI5NCAxNy4wMjAyIDI0LjkwODQgMTcuMTEyNiAyNS4wMDY3TDIyLjAwMTYgMzAuMDk0NkwzNC43ODMyIDE4LjYyODZDMzQuODgyNyAxOC41Mzc2IDM0Ljk5OTIgMTguNDY3MyAzNS4xMjU5IDE4LjQyMTdDMzUuMjUyNyAxOC4zNzYgMzUuMzg3MyAxOC4zNTU5IDM1LjUyMTggMTguMzYyNkMzNS42NTY0IDE4LjM2OTIgMzUuNzg4MyAxOC40MDI1IDM1LjkxIDE4LjQ2MDRDMzYuMDMxNiAxOC41MTg0IDM2LjE0MDYgMTguNTk5OCAzNi4yMzA2IDE4LjcwMDFDMzYuMzIwNiAxOC44MDA0IDM2LjM4OTggMTguOTE3NSAzNi40MzQyIDE5LjA0NDhDMzYuNDc4NyAxOS4xNzIgMzYuNDk3NSAxOS4zMDY3IDM2LjQ4OTYgMTkuNDQxMkMzNi40ODE2IDE5LjU3NTcgMzYuNDQ3MSAxOS43MDczIDM2LjM4OCAxOS44Mjg0QzM2LjMyODkgMTkuOTQ5NSAzNi4yNDY0IDIwLjA1NzcgMzYuMTQ1MyAyMC4xNDY3TDIyLjYzMDEgMzIuMjcxQzIyLjQ0MzMgMzIuNDM5MyAyMi4yMDA3IDMyLjUzMjMgMjEuOTQ5MiAzMi41MzIyWiIgZmlsbD0id2hpdGUiLz4KPC9zdmc+Cg=="})}),(0,E.jsx)("div",{className:"formgent-connected-settings__content",children:(0,E.jsx)("span",{className:"formgent-connected-settings__title",children:(0,D.__)("Zoho CRM successfully connected","formgent")})}),(0,E.jsx)("div",{className:"formgent-connected-settings__action",children:(0,E.jsx)(I.AntButton,{type:"primary",danger:!0,onClick:async()=>{await t({zoho:{...e.zoho,connected:"0"}}),(0,n.doAction)("formgent-toast",{message:(0,D.__)("Zoho CRM successfully disconnected","formgent"),type:"success"})},children:(0,D.__)("Disconnect","formgent")})})]}):(0,E.jsxs)("div",{className:"formgent-zapier-form-block-wrap",children:[(0,E.jsxs)("div",{className:"formgent-zapier-form-block",children:[(0,E.jsx)("h4",{className:"formgent-zapier-form-block__title",children:(0,D.__)("Step 1: Register your application at Zoho API Console","formgent")}),(0,E.jsxs)("span",{className:"formgent-zapier-form-block__subtitle",children:[(0,D.__)("To create Client ID and Secrete use the information below. Visit ","formgent"),(0,E.jsxs)("a",{href:"https://api-console.zoho.com/",target:"_blank",children:[(0,D.__)("Zoho API Console","formgent"),(0,E.jsx)(Q.A,{})]})]}),(0,E.jsxs)("div",{className:"formgent-zapier-form-field",children:[(0,E.jsx)("span",{children:(0,D.__)("Client type","formgent")}),(0,E.jsx)("div",{className:"formgent-zapier-form-field-input",children:(0,E.jsx)(I.AntInput,{className:"formgent-form-field",value:"Server-based Applications (No other type allowed)",disabled:!0,readOnly:!0,onChange:()=>{}})})]}),(0,E.jsxs)("div",{className:"formgent-zapier-form-field",children:[(0,E.jsx)("span",{children:(0,D.__)("Homepage URL","formgent")}),(0,E.jsxs)("div",{className:"formgent-zapier-form-field-input",children:[(0,E.jsx)(I.AntInput,{className:"formgent-form-field",value:formgent_data?.home_url||"",readOnly:!0,onChange:()=>{}}),(0,E.jsx)("span",{ref:j,children:(0,E.jsx)(I.AntButton,{className:"formgent-form-field-copy",children:(0,E.jsx)(s.A,{src:y})})})]})]}),(0,E.jsxs)("div",{className:"formgent-zapier-form-field",children:[(0,E.jsx)("span",{children:(0,D.__)("Authorized Redirect URL","formgent")}),(0,E.jsxs)("div",{className:"formgent-zapier-form-field-input",children:[(0,E.jsx)(I.AntInput,{className:"formgent-form-field",value:`${formgent_data?.admin_url}?fg_zohocrm_confirm=1`,readOnly:!0,onChange:()=>{}}),(0,E.jsx)("span",{ref:a,children:(0,E.jsx)(I.AntButton,{className:"formgent-form-field-copy",children:(0,E.jsx)(s.A,{src:y})})})]})]})]}),(0,E.jsxs)("div",{className:"formgent-zapier-form-block",children:[(0,E.jsx)("h4",{className:"formgent-zapier-form-block__title",children:(0,D.__)("Step 2: Enter the Zoho CRM credential below","formgent")}),(0,E.jsx)("span",{className:"formgent-zapier-form-block__subtitle",children:(0,D.__)("Paste the Zoho CRM Client ID and Client Secret you just received from the Zoho API Console.","formgent")}),(0,E.jsxs)("div",{className:"formgent-zapier-form-field",children:[(0,E.jsx)("span",{children:(0,D.__)("Data Centre","formgent")}),(0,E.jsx)("div",{className:"formgent-zapier-form-field-input",children:(0,E.jsx)(I.AntSelect,{value:i.filter(M=>M.value===e?.zoho?.data_center)[0]?.value,onChange:N=>{M(N,"data_center")},placeholder:`--${(0,D.__)("Select a data center","formgent")}--`,options:i,popupClassName:"formgent-field-zoho-data-center"})})]}),(0,E.jsxs)("div",{className:"formgent-zapier-form-field",children:[(0,E.jsx)("span",{children:(0,D.__)("Client ID","formgent")}),(0,E.jsx)("div",{className:"formgent-zapier-form-field-input",children:(0,E.jsx)(I.AntInput,{className:"formgent-form-field",value:e?.zoho?.client_id||"",onChange:N=>M(N.target.value,"client_id")})})]}),(0,E.jsxs)("div",{className:"formgent-zapier-form-field",children:[(0,E.jsx)("span",{children:(0,D.__)("Client Secrete","formgent")}),(0,E.jsx)("div",{className:"formgent-zapier-form-field-input",children:(0,E.jsx)(I.AntInputPassword,{className:"formgent-form-field",value:e?.zoho?.client_secret||"",onChange:N=>M(N.target.value,"client_secret")})})]}),(0,E.jsx)(I.AntButton,{type:"primary",onClick:N,children:(0,D.__)("Authorize","formgent")})]})]})}function S(){const{updateZohoSettings:M,updateSettings:N}=(0,g.dispatch)(T.A.GLOBAL_SETTINGS),{settings:e}=(0,g.useSelect)(M=>{const{getSettings:N}=M(T.A.GLOBAL_SETTINGS);return{settings:N()}},[]);function t(N,t){const g={...e?.zoho,[t]:N};M(g)}return(0,c.useEffect)(()=>((0,n.addAction)("formgent_after_global_settings_update","formgent",M=>{const N=M?.redirect_url;N?window.location.href=N:console.warn("No authorize_url found in response")}),()=>{(0,n.removeAction)("formgent_after_global_settings_update","formgent")}),[]),(0,E.jsx)(x,{children:(0,E.jsx)(I.Collapse,{icon:"data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDIiIGhlaWdodD0iNDIiIHZpZXdCb3g9IjAgMCA0MiA0MiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB4bWxuczp4bGluaz0iaHR0cDovL3d3dy53My5vcmcvMTk5OS94bGluayI+CjxyZWN0IHdpZHRoPSI0MiIgaGVpZ2h0PSI0MiIgZmlsbD0idXJsKCNwYXR0ZXJuMF84NjkzXzQ3NzE3KSIvPgo8ZGVmcz4KPHBhdHRlcm4gaWQ9InBhdHRlcm4wXzg2OTNfNDc3MTciIHBhdHRlcm5Db250ZW50VW5pdHM9Im9iamVjdEJvdW5kaW5nQm94IiB3aWR0aD0iMSIgaGVpZ2h0PSIxIj4KPHVzZSB4bGluazpocmVmPSIjaW1hZ2UwXzg2OTNfNDc3MTciIHRyYW5zZm9ybT0ic2NhbGUoMC4wMDE5NTMxMikiLz4KPC9wYXR0ZXJuPgo8aW1hZ2UgaWQ9ImltYWdlMF84NjkzXzQ3NzE3IiB3aWR0aD0iNTEyIiBoZWlnaHQ9IjUxMiIgcHJlc2VydmVBc3BlY3RSYXRpbz0ibm9uZSIgeGxpbms6aHJlZj0iZGF0YTppbWFnZS9wbmc7YmFzZTY0LGlWQk9SdzBLR2dvQUFBQU5TVWhFVWdBQUFnQUFBQUlBQ0FZQUFBRDBlTlQ2QUFBQUFYTlNSMElBcnM0YzZRQUFBRVJsV0VsbVRVMEFLZ0FBQUFnQUFZZHBBQVFBQUFBQkFBQUFHZ0FBQUFBQUE2QUJBQU1BQUFBQkFBRUFBS0FDQUFRQUFBQUJBQUFDQUtBREFBUUFBQUFCQUFBQ0FBQUFBQUFMK0xXRkFBQkFBRWxFUVZSNEFlMmRYNHhkeFozbnorMzJQL0FmMmdZM29KRFF0cFZSaUlUSEtKR3dXUWs3dTNZRWJFWjRSSFpYd2lzRlhtQ2x6UXFhZVlDSFlZWk04Z0F2TnRHdzBzSUxSRnJuWVNJMFJrSUJ4ZDZOc1RUQlBHVHR3UkprSXJsdEdEUEJmOENOLy8vcnZsdmYwMzNhdDIvZlArZmNlK3FjcWxPZmtycnZ2ZWVlVTZmcVUzVlBmZXRYdjZxcVJRU25DRHl3NDhqSVJCUU5EVWJSdW9HQmVUZE5UazR1SDZqVjdxeWJZeWFoK2h0cFNIRGorNGJEdklVQUJDQlFHSUdqVTNlcWowZFJ6ZnhGNHpYek4xbXZmekl3TUhDNlBubnRFL1A4R3I4VVJRZjNqcTdTOXdSSENKaHlJcFJCWU11T0krdlV5TmVpZ1hXMVd1MG04d1BaWk5LaEJsNS9CQWhBQUFJVkpWQS9hSVRDMFhxOS9zKzFhUExndFNnNnVudDBsVGxHS0pvQUFxQUE0dXJWbTl0c1VtTWYxYUtOcHZMck13MjlnVUNBQUFRZ01FMWdiMlJFUVgxZzhNREU1TlYvUmhUWXJ4Y0lBQXVNMWJ1ZlB6QnZvMUc0bTB6MCtxT3hOeEFJRUlBQUJESVFHRGZuN2pVVzByMVhKNis5aHlESVFDN2xxUWlBbEtBNm5iWnB4NUdoUlZHMGRhQTJ1TkdZOHJlYWMybndPd0hqT3doQUFBTFpDUncxRGRiZWVuM2lyWXZtRlgrQzdBQ2JyMEFBTkJOSitWbU4vbzBEODM1a2V2bHE4RGVsdkl6VElBQUJDRUFnSHdKNzY3V0JONkxKcSsrOU83cnFhRDVSaGhVTEFpQkRlZFBvWjRERnFSQ0FBQVNLSTRBWTZJRTFBaUFGTk9QRXQ2bFdHL3hiYytvNjg0ZDVQd1V6VG9FQUJDQlFCZ0hUcUwweFdaLzRoYkVLN0Mzai9qN2RFd0hRcHJTbWUvdFBHUlAvMCtZVUd2MDJuRGdNQVFoQXdGRUNSNDBENGN1VGs5ZmVZb2lnZFFraEFKcTRUUGYybnpLSHR6Wjl4VWNJUUFBQ0VQQ1FnS3dDVitzVFAyY213ZXpDUXdCTTgyZ3c4MithalloUEVJQUFCQ0JRRVFLeHI4QzdUOTN4aTRya3A2OXNCQzhBSHRqeDZXTzFXdlFqUTNGVFh5UzVHQUlRZ0FBRWZDRncxTXdnZUNGMElSQ3NBSGh3eDZkYnphcDhPMHh0SGZHbHhwSk9DRUFBQWhESWxVRFFRaUE0QVlDcFA5Y2ZENUZCQUFJUXFBS0JJSVZBTUFLQWhyOEt2MUh5QUFFSVFNQXFBYTAwK0hnb3N3WXFMd0EwbmUrR3FUbjhtczVIZ0FBRUlBQUJDSFFrWUJwR3JTWHdrNm9MZ1VvTGdJZCsvcStheC8rQ0tXbm04WGVzN253SkFRaEFBQUpOQkk2YTNRbC8vczdvblM4M0hhL014MG9LQU16OWxhbWZaQVFDRUlCQTJRU09tbUdCNzFYUkdsQXBBWUM1dit6ZkNmZUhBQVFnVUUwQ1psWEJGMzc5MU5kL1VxWGNWVVlBVFBmNlh6ZUZNMUtsQWlJdkVJQUFCQ0RnRElGS1dRTUduTUhhUjBJZWZQblRIV2F6bnQrYUtFYjZpSVpMSVFBQkNFQUFBcDBJakppMjVvanhMOVBtY040SHJ5MEFwdGR2Q21QZ0g2T290czc3a2lBREVJQUFCQ0RnRXdIdnJRSGVXZ0RrNFcrVTJBRWFmNTkrTDZRVkFoQ0FRR1VJeUJwdzRNRWRuM2c3eGR3N0N3Q09mcFg1OFpBUkNFQUFBbFVoOFBKRnMyN0EzdEZWNHo1bHlDc0JNR1h5WjZ6ZnB3cEdXaUVBQVFnRVFzQzdJUUZ2aGdDbXZmeU55UjlIdjBCK1RHUVRBaENBZ0U4RU5DVHcyd2QzSE5ucVM2SzlFQUR5dUJSWUEzWElGN0NrRXdJUWdBQUVnaU13RXRVRy85R1hXUUxPRHdGb2lwK3BRdDQ2V1FSWC9ja3dCQ0FBQVFpSXdNdnZQUDJOVVpkUk9Dc0FwcDM5ekJTL2FKUExBRWtiQkNBQUFRaEFvQTJCdmNZNThDOWRkUTUwVWdBd3Y3OU5WZUl3QkNBQUFRajRSc0JaNTBEbkJBQ2UvcjdWYmRJTEFRaEFBQUpkQ0RncEFwd1NBRFQrWGFvUVgwTUFBaENBZ0s4RW5CTUJ6c3dDZUdqSGtYWFRudjRqdnBZdTZZWUFCQ0FBQVFpMElSQlBFMVJIdDgzM2hSOTJ3Z0tneHIvT05ML0NDNThiUWdBQ0VJQkE0UVRHcjlVbnZyZDdkTlhCd3UvY2RNUFNCY0MwMlY4TC9EREh2Nmx3K0FnQkNFQUFBcFVrTUY2dlQ5eno3dWlxbzJYbXJ0UWhnSVl4ZnhyL01tc0I5NFlBQkNBQWdTSUpER25JdSt6aGdOSXNBQTJOLzBpUjFMa1hCQ0FBQVFoQXdCRUNwVG9HbGlJQWFQd2RxWG9rQXdJUWdBQUV5aVpRbWdnb2ZBaEFLL3poN1Y5MmZlUCtFSUFBQkNEZ0NBRXpPMkRnSDlVMkZwMmV3Z1hBRFdhakJKUEprYUl6eXYwZ0FBRUlRQUFDYmhLb3JadHVHd3ROWHFFQ1lIcGpuMDJGNXBDYlFRQUNFSUFBQk53bnNHbTZqU3dzcFlVSmdPbnRFWjh1TEdmY0NBSVFnQUFFSU9BWGdhY2YzUEZKWWUxa0lVNkFEKzc0ZEd0VWkyVDZKMEFBQWhDQUFBUWcwSUdBV1NQZ2UyYU5nTDBkVHNubEsrc0NBSS8vWE1xSlNDQUFBUWhBSUJ3Q2hTd1VaRlVBeUt2Uk9EWm9sYitSY01xTm5FSUFBaENBQUFUNkpuRDBvbGt0Y08vb3F2RytZMm9UZ1ZVZkFOUDQvNjI1NzBpYmUzTVlBaENBQUFRZ0FJSFdCRWFtMjlEVzMrWncxSm9BTUU1L1Q1bjBGZWJNa0FNTG9vQUFCQ0FBQVFpNFJNQ3FVNkNWSVlEcGNYK1ovb2RjSWtsYUlBQUJDRUFBQXA0UnNPWVBZTVVDTUwzU0g0Mi9aN1dNNUVJQUFoQ0FnSE1Fek9xNUExWm0wZVV1QUtibis0ODRoNUFFUVFBQ0VJQUFCTHdrVUZ0blk1R2dYSWNBcGszL1I3emtTNkloQUFFSVFBQUNEaFBJZTMyQVhDMEEwNlovaC9HUk5BaEFBQUlRZ0lDZkJFd2IrM3FlbXdibEpnQXcvZnRab1VnMUJDQUFBUWg0UXlEWHFZRzVEQUZnK3ZlbThwQlFDRUFBQWhEd25FQmVRd0c1V0FBdy9YdGVtMGcrQkNBQUFRaDRROEMwdVZwa3IrL1F0d0NZWHZCbnBPK1VFQUVFSUFBQkNFQUFBbWtJYk1wajE4QytoZ0RZNkNkTk9YRU9CQ0FBQVFoQUlIY0M0MmF2Z0ZYOTdCWFFsd1dnRmcyOFlMSTBrbnUyaUJBQ0VJQUFCQ0FBZ1U0RXRObGVYME1CUFZzQWNQenJWQzU4QndFSVFBQUNFTEJQb0IrSHdKNHRBSnFQYUQ5cjNBRUNFSUFBQkNBQWdYWUUrbkVJN0VrQVBMRGowOGRNWWphMVN4REhJUUFCQ0VBQUFoQW9oTUFtWTVIZjFNdWRlaElBdFZyVTE3aERMd25sR2doQUFBSVFnQUFFNWhMbzFRcVFXUUJNOS81SDVpYUJJeENBQUFRZ0FBRUlsRURBVEFzOHNqWHJmVE1MQUhyL1dSRnpQZ1FnQUFFSVFNQXlnZHJnanF4M3lDUUE2UDFueGN2NUVJQUFCQ0FBZ1VJSWpEenc4Mk0veW5LblRBS0EzbjhXdEp3TEFRaEFBQUlRS0k1QXJUNzVXSmE3cFJZQTlQNnpZT1ZjQ0VBQUFoQ0FRT0VFTXMwSVNDMEFUTzgvazJtaDhHeHpRd2hBQUFJUWdFRGdCTExNQ0VpMUV1QkRPNDZ0cTljbUR3VE9sZXhEQUFJUWdBQUVuQ2VRZG5YQVZCYUFlalR4dFBNNUpvRVFnQUFFSUFBQkNFVEdDdkJVR2d4ZExRQ3MrWjhHSStkQUFBSVFnQUFFM0NGZ2RncGMzbTJud0s0V0FMUGpYK2JGQmR4QlFFb2dBQUVJUUFBQzRSRllGQTEwdGR4M0ZRREdscERLbEJBZVhuSU1BUWhBQUFJUWNKTkFMVVhiM1ZFQVRHOHdNT0ptOWtnVkJDQUFBUWhBQUFKdENBeDEyeVNvb3dBdzV2L0gya1RNWVFoQUFBSVFnQUFFSENiUWJVcGdXeWRBblA4Y0xsV1NCZ0VJUUFBQ0VPaE9ZTnc0QTY1cTV3ell3UUl3dUtsNzNKd0JBUWhBQUFJUWdJQ2pCSVp1NkdESmJ5c0FXUG5QMGVJa1dSQ0FBQVFnQUlHMEJHcTFoOXVkMm5JSUFQTi9PMXdjaHdBRUlBQUJDUGhGb04yYUFHMHNBSmovL1NwZVVnc0JDRUFBQWhCb1RhRGRNRUJMQVlENXZ6VkVqa0lBQWhDQUFBUzhJOUJtR0dET0VNRFdIVWVHTHRjR1QzdVhRUklNQVFoQUFBSVFnRUJMQXEyR0FlWllBQzVIbVA5YjB1TWdCQ0FBQVFoQXdGTUNpNkxCT2N2Nnp4RUFVVlNmYzVLbitTWFpFSUFBQkNBQUFRaUlRQzNhMkF4aXJnQ28xZWFjMUh3Um55RUFBUWhBQUFJUThJZUFHZStmMDdtZkpRQWUybkZzbmNuT2lEOVpJcVVRZ0FBRUlBQUJDS1FnTUxSbHh4RzE4VE5obGdDb1J4T2JacjdoRFFRZ0FBRUlRQUFDbFNFd0x4clkxSmlaV1FMQWJQMkwrYitSRHU4aEFBRUlRQUFDRlNGUWo2Sk5qVm1aTFFDYXZtdzhrZmNRZ0FBRUlBQUJDUGhMb05iVXlaOFJBTlBqLzBQK1pvMlVRd0FDRUlBQUJDRFFnY0RRQXp2K05KSjhQeU1BSnFQSldjNEJ5UW04UWdBQ0VJQUFCQ0JRRlFKWE55VTVtUkVBdGFpT0FFaW84QW9CQ0VBQUFoQ29JSUhHdG41R0FCZ0h3RCt2WUY3SkVnUWdBQUVJUUFBQ0NZR0dCWUd1Q3dBY0FCTTh2RUlBQWhDQUFBUXFTcUEya21Rc0ZnRFREb0RKTVY0aEFBRUlRQUFDRUtnbWdaa0ZnV0lCVUk4bVI2cVpUM0lGQVFoQUFBSVFnRUFqZ2NGb01QYjVteFlBT0FBMnd1RTlCQ0FBQVFoQW9Lb0VFa2ZBV0FDWXhRRndBS3hxU1pNdkNFQUFBaENBUUFPQmVxMTJrejdHQXNEc0V6aGkzaE1nQUFFSVFBQUNFS2c0QWJNejRDWmxjVm9BTUFSUThmSW1leENBQUFRZ0FJR0VRTHpxYjIzcmppTkRsMnVEcDVPanZFSUFBaENBQUFRZ1VHMEM5ZnI4VlFPWG9vZ1ZBS3RkenVRT0FoQ0FBQVFnTUl2QVJIUnBhS0FXRGNhbWdGbmY4QUVDRUlBQUJDQUFnY29TMEZSQTR3TlFINmxzRHNrWUJDQUFBUWhBQUFKekNKaXBnRU1EOVNqQ0FqQUhEUWNnQUFFSVFBQUMxU1dndGwrekFFYXFtMFZ5QmdFSVFBQUNFSURBSEFLMTJwMERaaEdnZUVHQU9WOXlBQUlRZ0FBRUlBQ0JhaEtvMTVmTEFzQVFRRFdMbDF4QkFBSVFnQUFFV2hNd25YOGpBR29JZ05aNE9Bb0JDRUFBQWhDb0pBR3pHdUNJWmdFZ0FDcFp2R1FLQWhDQUFBUWcwSjZBaGdBSUVJQUFCQ0FBQVFnRVJrQUNZQ1N3UEpOZENFQUFBaENBUU9nRU5BUkFnQUFFSUFBQkNFQWdOQUlJZ05CS25QeENBQUlRZ0FBRURBRUVBTlVBQWhDQUFBUWdFQ0FCQkVDQWhVNldJUUFCQ0VBQUFnZ0E2Z0FFSUFBQkNFQWdRQUlJZ0FBTG5TeERBQUlRZ0FBRUVBRFVBUWhBQUFJUWdFQ0FCQkFBQVJZNldZWUFCQ0FBQVFnZ0FLZ0RFSUFBQkNBQWdRQUpJQUFDTEhTeURBRUlRQUFDRUVBQVVBY2dBQUVJUUFBQ0FSSkFBQVJZNkdRWkFoQ0FBQVFnZ0FDZ0RrQUFBaENBQUFRQ0pJQUFDTERReVRJRUlBQUJDRUFBQVVBZGdBQUVJQUFCQ0FSSUFBRVFZS0dUWlFoQUFBSVFnQUFDZ0RvQUFRaEFBQUlRQ0pBQUFpREFRaWZMRUlBQUJDQUFBUVFBZFFBQ0VJQUFCQ0FRSUlGNUFlYVpMSHRFWUhqcHZPaldaWVB4MzVLRmc5SGloYlZvZU5sVXRiMTE1blV3ZFk2T241bUl6ejEvZVRJNlovNU9uTGtXZno1dVh2V2RqaDgvTy9XYU9sSk9kSjZBNnRHUzZicWpWOVdkeG5xa2VyVms0VlIvU0srTHA5KzN5bGhTZC9TZDZ0RDV5L1g0Tk5VaDFTZDkvangrTlhYSjFLa1RaNmZxV0t1NE9BYUJNZ2tnQU1xa3o3MWpBbm80cjE0NVA3ck5QSlJYbVZlOTEwTTRhZUR6eEpRMlRqM2s5UkEvWVI3Z1l5ZXZSSWRQWG8zRndkaXBxZGM4MDBSYytSQlFvMzNyMHNHNC9xeGV1U0N1UDBsZDZ0U2daNzI3NGtyaXUzWFd4UXRuZldyOGtBaE0xU1hWcWNQbWxiclVTSWozWlJDb1BmanlwMVB5dFl5N2M4L2dDS3krWlg2MDlvNUZNdzI5R3Yza1llb0xERDI4OVJBL2RPd3lEL0tTQ2sxMVJuVnBqV25vSlJyWDNySFFpbUMwblQwSlRZbExDWU1QVFgyU0tFaXNVcmJ2VGZ3UVFBQlFCNndSU0I3U0c5YmNZSHBsQzh6RGVyNTNqWDFhT0hHUHpqekkzejk4a1lkNFdtZ1p6bE5kMnJCNlVWeVA3amFOdlJyK3FnWlpDOGFTdWpSdEthaHFYc2xYdVFRUUFPWHlyOXpkNy83YXdyaUhyNGUwZW1XaGhrWkJjT2l6eS9Id1FhZ3Nlc2wzbzNpc2VvUGZqWThFZ2F3RHNqaDlhT29TRm9KdXhQZytMUUVFUUZwU25OZVNnQjdVYXZUVnk3L1AvT2t6WVM0QlBjRDNmSFNlQi9oY05MT09xQzV0VzM5VHBhMUZzekxjd3dmVnBmM0cwdlM3c1l1SWdSNzRjY2wxQWdpQTZ5eDRsNUpBWW83ZC9PMGxQS2hUTW1zOFRkYUIvL1BSQlI3Z0RWRGtuRG02WlVYUVZxTUdIS25mVXBkU28rTEVGZ1FRQUMyZ2NLZzFBZlhPdHQ2ek5INUkwOU52elNqclVTd0RVYlQ1cnNYUmt4dUhzQjVsclR4TjUwc012SFhnSEZhbUppNThiRThBQWRDZURkOFlBcXR2V1JDYjk3ZmVzNFFIdE9VYXNkc01FV2lZUUQ0RG9RUTEvczk4ZjBVbzJTMHNueUhXcGNMZ1Z1aEdDSUFLRldhZVdWbS8rb2FaM242ZThSSlhkd0p5K3RxNS8wemxlM0l5KzcvKytPM2RnWEJHendSQ3FVczlBd3I4UWdSQTRCV2dNZnN5NjI5ZHQ5UTAvUFQyRzdtVStWNDl1WjBmbktta3M1Y2EvN1FMTTVWWkJsVzVkNVhyVWxYS3FPaDhJQUNLSnU3Zy9mUVFmbmpka21qTHR4ZGo1bmV3ZkpRaytRcnMzUDlWWllZSE5wdTY5b3h4K2lNVVQwQnJWZXc2Y0xZeWRhbDRndFc1SXdLZ09tV1pPU2R5NmxPanI0Y3h3UThDaWFQWG5vL1ArNUhnTnFsODhaR1Y4WG9SYmI3bWNBRUVFZ2RVMyt0U0FhZ3Fld3NFUUdXTHRuM0dtSExWbm8wdjN5Ump1NzQrdkgvOTFOZDlRVjM1ZFBwZWx5cGZRQll6aUFDd0NOZTFxTlh3Yjd0M0dUMSsxd3Ftai9UbzRiMzlOMTk2WmM2VjVlbWxIdzcza1dzdXRVR2dhc05NTmhoVkxVNTJBNnhhaWJiSUQ4NTlMYUJVNUpCRW5ScFRueHk4V0VQQ3pjcW5wYnZYZWxhWDNDVHBUNm9RQVA2VVZVOHBsVmYvdHZYTGNPN3JpWjQvRjhtWFEzOCtDSUVsaTJyK2dBMHdwVDdWcFFDTEo5Y3NJd0J5eGVsT1pES3phb0VWOVJBSjRSRFF3MXM5T2EwajRLcC93TGxMN0VEdVE0MzBvUzc1d05IbE5PSUQ0SExwOUpBMm1WYzF2VXFiOHhEQ0ppRC9nR2ZmUE9uY0dnSVNwU3dBNUZmZGRMVXUrVVhSdmRRaUFOd3JrNTVUaExtL1ozU1Z2bEJ6dm5kKzhKWFprdGlkbnZldi90c2RabGlLb1FEZktwNkxkY2szaGk2bGQvQ2JENHkrNEZLQ1NFdDJBdXBSUGYrRFc2S0gxaTZKRnN6am9acWRZTFd2K05idEM2T05mM1pqTEFER1RsMTFJclBMYnh5SWxDNkNYd1NTdXFSNmRPTE1oRitKSjdWekNDQUE1aUR4NjRCNi9jODllSFAwOVJYei9VbzRxUzJVd0JJek5LUmhJUTBSL2VIenk5SFZrcC9keDA1ZmkvZWFLQlFDTjh1RmdPcVMvQU5jcVV1NVpDclFTQkFBbmhZOHZYNVBDNjdrWkNjOU9PMDRlUHJDWkdtcE9YOTVNbTVBc0FLVVZnUjkzemlwUysrUFhUTFdwZkxxVXQ4WkNUZ0NmQUE4TFB6MXBpZjNWOGJSTDdUNTFISkVPbWNlTkRJOTZsVVBuZmp2U3QxOHZ0NmxQV2ZHdXBNSGtoZ3RhUmhydm5YcDFLeUlZVE5zb3A2TXhxRWxwdlFYV3RCTUFma0dsQlZVTnE4OGVwdGhQMWhvRXVRTG9mb3lkdkxxckhwMC9PeTFPQjJOOWFkZHdwSTBOOWFuNU5pYWxRdmpldFh1MmlvZUw3c3VWWkZwRVhsQ0FCUkJPYWQ3NklHNTdkNmI0dDM2Y29yU3VXajBjUDd3MktWSWpmMkpzeFBSNStaVkQrb1Q1dFYyRU4vVksrZkh3bXJOTGZQTit3WFJzR21jMXBqWHFnYnRMZkRUdDc4b2hHOHJoaEplTHo0eWJFVUVxQTVwZFR2VkhUWHVZeWZOcTNtZmlNTlc2Y256V0ZKL2JqTjUxTFJjaWMwcWl3T3gzcjc3eTlMcVVwNWxGMHBjQ0FCUFNsb1B5ci8rd2MyVmFveVN4bDdtYURYMGg4d0RwS2lIYzVaaVQ0U0JoSUFlNUZVVEJXb1VYOTAzSHUwM3U4U1ZFVlMzUjdjczcydHpvS1N4UDJLYzA5UVFGZG5RWjJXV0NJTzFwaTdkYmRac3FKTEFGSGVKQVAyV0NlNFRRQUM0WDBaUlZVeitqUTMrNzB4alUwU3YzbGJ4cXRGYVphd0Y5NjIrd1R6RUYxbnB3ZHBLZTd0NHl6YmphbGRLV2JnU1UzcTdkT3E0NnRMdWo4NUZZNmV1Uk84Zjluc01PaEdZRytLNlZBMUJVSFpkNmxSMytPNDZBUVRBZFJaT3Z0UG1QZHZXMytSazJ0SWtTdVo4OWZEVks2dHlyMENDUUwyNXpYZmQyRmRQTmcxVG0rZG9yL2p0dTcrSUcxaWI5K2tVdHdTdmhKVXNMZUthQkEwRkpYVnB6QXhkVkRVa2RVbUN3T2NGdmJRczlXdjdUcGRhbDZwYVIvTEtGd0lnTDVJNXg2TmV3WlAzRDNtNWM1OGEvZjNHTTlqM1huNnZSYXF5MDRQYjF3ZTR6TGd1cmlEWWEzbjRmSjNxa29TbEJKRXNKTDRGNnBMYkpZWUFjTEI4MUFQd2JieGZKdGxkQjg4WXMrd0ZyMDM3ZVZlSHBEZm5tMlZBRCs2ZnZuMHFkc0RNbXdueDlVWWdFWmJVcGQ3NGNkVmNBZ2lBdVV4S1BTSUhvZWVOczErajZiUFVCSFc0K2RTWS9rWFQ4SitydEhtL0E0Sk1YNmxNTmFUams4L0FhKytObS9JOW15bWZuR3lmZ0k5MWFmdHZ2blIyZ3lyN0plYm1IUkFBRHBXTFBNei81aTl1aWFlaE9aU3NPVWxKZXZ1N0RweHowbXQvVG9JZFBKREY0YTNzNU9QUVZYWUpkTDYvNnRMRDY1WjRNWnVBdXRTNUxJditGZ0ZRTlBFMjk5dDgxK0o0Kzk0Mlh6dHhXR1A3ZXo2K0VPMHh6ajJFZkFob2ZGZkxPYnZ1N01XRE81L3l0aG1MNnRJVzh4eHgzVmVBdW1TekZtU0xHd0dRalplVnMxMzM5RmZEdi9PRE01ajVyWlQrVktTSlNkZmxoN2U4dW5lWU9kNEV0d240VUplMHErQnJadTBKUXJrRUVBRGw4by9IaEYyZDVrZkRYM3psY1AzaDdjSTB3ZUpMeGM4N3VsNlhFSlRsMXlzRVFJbGw0R3JQLzdoWmEvOVZNMyszckpYaFNpd1NaMjZ0aDNlL3ErUFp5b3lXRDM3dXpSUE03N1lGT09kNFhhNUxpSUNjQ3p0amRPd0dtQkZZWHFlNzJQakx1ZTlYdno4VEwrVjV4Q3k2UWlpUGdKWkVsci9GY2JNZmdtYUdhT01pVjhLS3hZUFJkKzVjRk8zNzQ0WFN0eFYyaFluTDZYQzVMbWtaWkczTXRYK3NuR1dvWFM2M0l0S0dCYUFJeWszM2NMSHgxMVF2T2VlNHVCWi9FNzRnUDI1YnY4dzRDeTV6YXBjNUxBRitWa1hWSlMyNTdGTEFFbEJPYVdBQktKaTdhNDMvWWRQVGYrbmRMNkozRHAwM3ZibDZ3VFM0WFZvQ1drWlpQVzVaQW1RUmNDRmdDWENoRkxLblFYVkoxaVdYNmhLV2dPemxtTWNWQ0lBOEtLYU13N1hHWDN2QnYvU090b0tkU0prRFRpdVRnS3d6N3h0VHFVdkRBb2lBTW10RTcvZDJzUzRoQW5vdnoxNnZSQUQwU2k3amRacm4vK1RHNVJtdnNuTzZldjNQL01NSm5QenM0TFVlcXpiRjBaaXBLejA0UklEMUlyZDJBOWZxMHRUV3lMVjQweWRybVNiaUdRSUlnQmtVOXQ0a0svelp1MFA2bURYVy96T3p4anRqL2VtWnVYaW1hejA0UklDTHRTUmRtaHJyMGxxenRmV0NlYlYwRjFvNmE2MVowQ2lLRUFHVzhNNktGZ0V3QzBmK0h6UUY1NlVmRHBmK285TFVQbTN1b3JGK1FuVUlKRDA0N1MrZ1Jyak1nQWdvazM3LzkxWmRrcC9KaGpVM2xqN3JSQ0pBejZ5eFU4eEc2cjlrMjhlQUFHalBwdTl2MVBpLytNakthSG5KRDJZdDN2TDhXeWVqWTZldjlaMG5JbkNQZ0hwdzd4dzZwMDVUcEI1Y21RRVJVQ2I5L3UrdHV2U1dObjl5b0M1cGVld1BQN3VNajFML3hkbzJCZ1JBV3pUOWZhR3RPN2YvbCtIU2QvVjd6U3pvb3lVMzhmRHZyeng5dUZyZTNZZE5qK2xidHkwc3RRZUhDUENodG5ST28rcVNuRTNMSGhLNHo0aUE5LzU0a1NITHpzWFY4N2Z1ckM3U2N4YmN2UERKKzRkS2JmeTFxTSt6WnJVMjdkaEhDSWVBVm0vVUtuMHluNVlaNU16MTRpUERUcTFiVUNZUEgrK3RUYjkrL012UFM2MUw2a2k5Wkt5b2l4ZVc2NWZnWS9tbFNUTVdnRFNVTXA2ajZYNWI3MW1hOGFyOFR0ZkRmL1FmamtlczVwY2ZVNTlpa2hsM3o4Zm5vd1dEdGVoYnQ4dWhxcHlBSmFBYzdubmVOYWxMWDE4K1AvcjZpdmw1UnAwNkxzMTJrVlZMZFpxUUx3RUVRTDQ4by9YR1pQVS8vdjJLbkdOTkg1MDI4SG51elpQUitJVnllNERwVTh5Wk5naG95T2YzbjF3cWZTd1hFV0NqZEl1TlUzVkp6b0ZsK2dYSW4wb0pPR1I4QWdqNUVVQUE1TWN5TnZrLzkrRE5wWTIvYW9yZlMrOTh5WGgvam1YcWUxUWF5ejEzcFI1OTE2emRYMVpBQkpSRlB0LzdxaTZWS1FJME0wQStMamd6NTFldUNJRDhXRWF2UEhwcmFlUCtPejg0RTczeFQxL2xtQnVpcWdxQmYvbjhTdnpnL082ZE41UTJIUlVSVUkzYWxEZ0h5a08vakNBaGkxTmdmdVJ4QXN5SjVSUDNMeSsxOGQrNW44WS9wNktzWkRSeURwUlRxSnhEeXdvNEJwWkZQdC83VGprSEhpK2xMc2twOFBrZjNJeFRZRTVGaWdESUFlVG1ieTgyVG45TGNvZ3BleFRiZDM5cGR2R2o4YzlPTHJ3cnhrNWVLZDJyR3hGUWpYcW51bFNXb0ZRZGNtMDNRMTlMRlFIUVo4bkpPVVZlLzJVRU5mNVM0d1FJcENWdy9NeTEwcWNKSWdMU2xwYmI1NVVwQWpUTDZ1NTR5V0MzR2JtZU9nUkFueVUwdW1WRkthWi9HdjgrQ3k3Z3l4RUJBUmQremxrdlV3UThZNTY5ckEvUVg0RWlBUHJndDNYZFVyTlNWdkh6ckxXNkh6My9QZ3FPUzgzaUxsZ0NxQWI1RUNoTEJNajYrc3lXbS9QSlJLQ3hJQUI2TFBqWTlMKytlTk8vdlAxWjNhL0hRdU95V1FRUUFiTnc4S0VQQW1XSkFNMUdVRWVNMEJzQkJFQnYzQ0taL3VXUldtUlE0NC9EWDVIRXEzOHZSRUQxeTdpb0hFb0V2R3FzazBXSGJhWWp4bEJBYjlTTGJjRjZTNk56VjhucnYyalR2eGI1b2ZGM3JpcFVJa0dJZ0VvVW94T1owTkRrcTJienNTS0RPbUlNQmZSR0hBR1FrVnNaWHYvYXp2ZTE5NHI5VVdYRXd1bWVFMEFFZUY2QURpWC9yUU9tcy9KQnNWT1ROUlRBcklEc2xRQUJrSkdacHZ4SkJCUVZ0TEdQUFA0SkVMQk5BQkZnbTNBNDhlL2NmNmJ3elh1WUZaQzlmaUVBTWpCYnYvcUdTT2Ivb29JYS82bkZOaWFMdWlYM0Nad0FJaUR3Q3BCajlsODFWc3ZESjYvbUdHUG5xTlF4MjdxdWVNZnN6cWx5KzFzRVFJYnllWExqVUlheit6OTErKzR2b2hObXVoWUJBa1VTUUFRVVNidTY5OUpXd2o5Nys1U1pjbHJjenFSeUNCd3UwRUxyZStraEFGS1dvS2FhRkduNmw4ZC92UHRXeXZSeEdnVHlKSUFJeUpObXVIR3BIcWtqVTJUUVVBQWhIUUVFUUFwT2F2Z2ZMbkN0LzkzR2t4YVAveFFGd3lsV0NTQUNyT0lOSm5KMVpJcWNHYUFaV2pnRXBxdGVDSUFVbklwMC9KTzVUTDEvQWdSY0lJQUljS0VVL0UrRFpnWjhlT3hTWVJsaHM2QjBxQkVBWFRpcDkxK2s0OTlQelpnWjQvNWRDb1d2Q3lXQUNDZ1VkMlZ2OXRPM3Z5ak1IMEJXZ00xM0ZlZXc3V3VoSVFDNmxGeVJPLzJwNTYvVnRBZ1FjSTBBSXNDMUV2RXZQWElLTE5JZmdCVUN1OWNSQkVBSFJrWDIvbVBULy81aUY4L29rSFcrZ3NBY0FvaUFPVWc0a0pHQS9BRjJtZUdBSW9LZTMwd0w3RXdhQWRDQlQ1RzlmODMzSjBEQWRRS0lBTmRMeVAzMHlkSloxTlRBcmNaNW0zMEMydGNKQkVBYk5rWDIvdldEWU55L1RVRncyRGtDaUFEbmlzU3JCQlU1RktCOUFyQUN0SzhlQ0lBMmJJcnEvV1A2YjFNQUhIYWFBQ0xBNmVKeFBuRkZEZ1ZnQldoZkhSQUFMZGdVMmZ2SDlOK2lBRGprQlFGRWdCZkY1R3dpaXhvS2tCVmdDek1DV3RZREJFQUxMRVgxL3JYZ0Q2Yi9GZ1hnMEtIaHBmT2lWbjhPSmJIVXBDQUNTc1h2OWMwMUZQRHF2dE9GNU9IaGU1WVdjaC9mYmxMY3RuYWVrQ21xOTgrQ1ArNVVDUFVRN3Y3YXd1ZzI0elc4YXVYOGFMWDVXMktPcVM1MENucUFmVzZXT2oxaEZtL1M5RTF0ZkRKMjZtcHdvaTRSQVM4K01teVlEWFpDWnUyN05Tc1hSTHIvYzhhWjl2emx1clg3RUhHK0JQYWJyYzYxUU5EYU94YmxHM0ZUYlBvdGEzVkFsbGVmRGFiMjRNdWY4bXRwWUtMZS83YjFOelVjc2ZOV1cvenVNUllBUWprRTFPQnJEM0g5ZFd2b3M2YndzQkVEWTBZTXZHOGVidnZITG1hOTNOdnp4YkZNRVNCd1lvOEk4S3NLclRiaTdaVkhiN1dlNkEvTkZFVFZEY0oxQWdpQTZ5emlkNjgvZm52dURVTFRMZUlwTUkrLy9tL05oL2xzbVVEUzZHOHhXenFyMTE5RWtKWGdkMFlJU093ZCt1eHlFYmNzOVI2SWdGTHhlM3Z6Sis0ZmlyWVdZS2FYenhWV2dPdlZaUENiRDR5K2NQMWoyTyswNUs4YUI5dmh0WDNqY1EvUjluMklmNHFBeXZXSis1ZEgvOVZZZHI1MSs4Sm93YnhhWVdoMEw1bW5WYStVRHBtbk5VeFExU0RCSTZ2SGhqVTN4c01vWmVSenhlTEI2RHQzTG9yMi9mRkNkTFc0bldqTHlHcGw3dm1IejY5RS8vSHVwWVg4TmtPeXluV3JJTVYwZzdxbHdwSHZOOTkxby9XVWFPd2YwNzkxelBFTjFPREtvcVB0UWJVMmVObEJ2ZU5udnI4aVRsT1YxeWxQZkFLS1d1eWxWYmttUGdFc0F0T0tqbnZISkJ4M0hiQy9DZHA5WnNpUE9uRzkvTEVBVExQUXcvbkpqY3V2azdIMGJtcXpIN29sbHZERzBjclUvOHozYjQ1TmluTG1jeTBvVGZJOXFMSkZBRXVBYTdYTy9mVElNbWJiQ2lDTDNOVnJVUkREY1dsSzNMMm5ZNXBVV3ppbmlLbC9ja0poL01sQzRVMUhxWEY5bWZwZit1R3dFejMrYmpsTkxBS2p4a0l4YkFSbzFRS1dnS3FWcU4zOEZHVUYyTERHN293RHU1VHlqUjBCTU0xVFUwUnNoNTBmc05tUExjYnE5Y3VUV0t0KytSYmtIL0RTSXlzcnVYMHBJc0MzMmxodWVuY2RQR2Q5R3FkbUhSVHh2QytYWkxxN0l3QU1wL1dyODU4SzFveGY0NkgwL3B1cDVQTTU2ZldyUisxclNLd0I4b2F1V2tBRVZLMUU3ZVduTUN1QWVlWVRvZ2dCWUdwQkVaNy85UDd6LzduSjVLOTU1ejcyK3R2UjBGUW9PUzVXYlVnQUVkQ3V4RG5lVEtBSUs0Q2UrVGdESWdEaU9mOXl5TElaOFB6UG42NTZ6REw1dStEZG4zZnVsRGNOQ1NBQzhpWWJ4Vk15SlJwNStPZlBOcThZWlFYWS9kRzV2S0pyR1k4NkR4b0tDRDBFYndFb1lpeUkzbisrUHpNMWtDK2FCbEt2VlEzSzIvODBBa2ZMRWxjcFlBbW9VbW5heTh1ZWp5L1lpM3c2NW0zMzJsL3gxWG9tK3J4QjhBS2dpTG4vOHY0bjVFTWdoTVkvSWFWZXlrdW10NG9JU0lqazk4bzZBZm14dEJHVDl0YlFIZ0Uyd3hvanJrTzNCQVV0QVBTQXRiMEpCVHYrNWZjVERxbnhUNmdoQWhJUytiOGlBdkpubW1lTTJpN1ladEJ2YTBQZ3pvQkJDd0RiWS8rcXZIcytac09mUEg3RStyRlczZXpmamhNaW9CMlovbzhqQXZwbmFDc0d6WnF5dmJQajNaWjNJYlRGSnE5NHd4WUFsdFVmVS8veXFxWlJ2Snh2bGNmOHU1RkNCSFFqMVB2M2lJRGUyZG0rMHZieXdLRXZEUnkwQUxCdC9zZjVMNS9IZzFacExNSmFrMDlxN2NXQ0NMREhGaEZnajIwL01kdDJCdFJ2S3VUWkFNRUtBSzBjWjlzQkJPZS9mbjc2VTllcW5MYVpYZndJVXdRUUFmWnFBaUxBSHR0ZVk5YXNFZHZPZ0NIN0FRUXJBR3ozS05YNG56Q1ZsOUE3QVpuOHRYc2VZVFlCUk1Cc0hubCtRZ1RrU1RPZnVQWWZ0anNid0haYmtBOEZPN0VFS3dCc3ovL0grYS8vQ2l2VGY4amovcDBJSWdJNjBlbnZPMFJBZi96eXZucTNaVWRxUFdPR2wxWjNUWkZPNVJHa0FORERVejl5bXdIemYzOTBWOSt5SU40dXQ3OVlxbjAxSXNCZStTSUM3TEhOR3JOV0JyUTlETEQyRHJ2dFFkWThGM1Yra0FKQTQ4bzJBK2IvL3VrKy94YzM5eDlKQURFZ0F1d1ZNaUxBSHR1c01kc2VCZ2gxT21DUUFzRDIrdkg3RDl0ZnhqTHJEOGluOHplYmpUb3cvYWN2TVVSQWVsWlp6MFFFWkNWbTUvejN4eTdhaVhnNlZ0dHRndFhFOXhGNWtBTWZ0c2YvUC95TXBYLzdxSk9SeHY3TERQSThmdi93eFdqczFKVm83T1MxNkp3eFFTWU9uZHFnWjRrWlFocGVOaGl0TlpZazFTWGJ3MGxwV0NRaTROazNUNWcwWDAxemlSZm5KSHNIYUFPZld3M3pNa0lpQXA0emJHMHZURk5HL255NHArcUIvbXgxREJJL2dCTm53M0xjSHZ6bUE2TXYrRkFCOGtxakhwUlBibHllVjNSejR0SGlQMi84MDFkempuTWdIUUgxL292WW5yazVOWHF3Ly9yUXVlajEzMzBWdmJadlBQcjlKNWZpaHZUMGhRbnowSitjT1YzdmRlelk2V3Z4T2U4Y09tOVdlN3dRaTRMRkN3ZmoxNW1UQzM2ellGNHQydmhuTjhicE9uM2hlcG9MVGtidXR4UHovYVlIdUdITmphWHhYYkY0TVByT25ZdWlmWCs4RUYyZHlEMkxSSmlDZ0JycGI5MXViL2hXK3crTW5hcU9lRTZCTkFwdUNHRDFMWFozVjdQdHJKS21VSDArcDRqTm1acjVhTCtHeDE3L3Q3amgxL0tqV1lONkp0dDNmeG1waDFqMjRrK0pKWUFOaExLV1l2ZnpFMHVBN2ZWRHVxY2t6RE5zRHdPRXVDQlFjQUxBdHJsV1BSVkNid1NrOEcydnp0aVlNbGxyWkRMZllScnZ4bDUrNHpsWjNrc0k3TngvSm5yODlUOFpjMlY1M1VSRVFKWlN5M1l1SWlBYnJ6elB0ajIwaFFESXM3UWNqY3YyK1AvaENvMi9GbDJFUlk3OXE1elUrUGZTNCsvR1JVTGd4Ny84dk5TTm9CQUIzVXFwOSs4UkFiMno2K2RLaVhTYkZsWnREeHhhQ000Q0lPY3RXMEc5dnNSWnpOWTlxaHl2YlhHV3NGUGpMM085emJMU3cycjdiNzRzZFVnQUVaQ1VlUDZ2aUlEOG1hYUowYVlWUUwrWDBCWUVDa29BcUlCdERnSElpWVRRR3dHdHpXREx3N2N4UlVuam40Zkp2ekhlZHU4MUpGQ21Yd0Fpb0YzSjlIOGNFZEEvdzZ3eDJGNWdMYlFGZ1lJU0FMWWRBQThkczd0bWRkWWZpMC9uRjdFZXR5dzBQMzM3VkM3ai9WbllJZ0t5MEVwL3JvWmFaTWtwMDk4Q0VaQyt2UEk0ODVEbEtkYWgrUUVFSlFEVUc3SVpEZ2MyaFNSUGxrVjRyYXNuYnRQczM0a0hJcUFUbmQ2L1F3VDB6czdISzJXNVU1bmJDa1ZZSVcybHZaZDQ3YmFJdmFUSTRqVzJWM3RpQ0tDM3dwTXdzKzM5cjZsK2U4eGZtUUVSWUljK0lzQU9WMWRqdGVrSFVFUkh4Q1d1UVFrQW0rcE9sWkpWd25xcjJyYUhacFNxblIrYzZTMXhPVitGQ01nWjZIUjByb2lBSis2M3Q4aVlIWEwreFdxem8yV3pqWENSZEZBQ3dPNE1BSHRtS1JjclRwNXBzdW1ZcVhTcTkxK1c2YjhWSjBSQUt5cjlIM05CQkdnVnl5ZnVIK28vTThUUWxzQnh5OHYxaGpRVElDZ0JjTnN5ZS9NOGJhclN0citFaW54aFU1Z0owUjdMKzRuM1VneUlnRjZvZGIvR0JSR3c5WjZsMGRaMVM3c25sak42SW5Eb21OM1pWbXVHN2JVVFBXWFk0a1hCQ0FDTk05dGN3ak8wTmFUenJKTTJ4OTNrTkdSanNaODg4bzhJeUlQaTNEaGNFQUhiMWkrTGJOYnJ1YmtPNTRqSzEyWll2S0JtTTNxbjRnNUdBTmdlWjlhT2NZVGVDTmljbldGNzNuQnZPYjUrRlNMZ09vczgzNVV0QWxTbm4vL0JMVlk3SFhueThpMHVteUlncEttQXdRZ0FtNDJNZmp3TUFmVCtDQWw5YUFZUjBIdmQ2WFJsMlNKQURtWGI3cjJwVXhMNXJrY0NObWNDMkc0cmVzeXlsY3VDRVFDM21SK2pyU0F6TXpNQWVxZkwwSXlacGNDS2diMVhvQTVYbGkwQzVBL0FVRUNIQXVyeEs1c3JlWVkwRXlBWUFXQ3prU2x6SmJJZWZ6L09YR1piYmZzME5JTUlzRk10eXhZQlRBM012MXh0RGdIY2FuRy9tUHhKOUJkak1BTEFwcXJ6cVpIcHI3cmtmL1VTeTZzekh2K3F2RzE1ZTZHRkNPaUZXdmRyeWhRQldvQ3NxSTJ1dXBPb3hobTJwd0pXZzFMM1hBUWpBR3oyTkcycTBlNUZ5QmxWSTRBSXNGT2laWXFBWjdhc3NKT3BRR005ZDZsdUxlYzJPNHZXRXQxanhBRUpBSHRUTzJ5T1IvVllydDVjWnR0NmN1dE45clovdGdrWkVXQ0hibGtpUUkzSzVyc1cyOGxVZ0xHZU9HdlhzbWR6eU5pbDRncEdBTmlFamdEb25hNXRkcmN1OVZNQWlDZ2lvUGQ2MWVuS3NrVEFack5LSUNFZkFyYWZHNHNYK1B2Y3lFSTRHQUZnMDZ6REVFQ1dLamYzWEpzektIeWYwNHNJbUZ0ZjhqaFNoZ2pBRnlDUGtwdUt3N2JsTUwrVXVoMVRNQUxBN1dJSU8zWG5MdHN6NS9rdUFGUXpFQUYyZmg5bGlJQU5xMit3azVuQVlyVnRBZkIxNkRCck5RaEdBQ3haYU0ra2MvNktQWWVVckFYcTQvazJGL1ZZczNKK0pWWmpRd1RZcWRsRml3QnRGaFRLK0xLZEVpUFdQQWtFSXdCcy91Z3dSL1ZYSlcwT29XajJSeFdzQUNLTUNPaXZuclc3T2hFQk5vZWlrbnVyUG1JRlNHajA5MnJ6dWRGZnl2eTVPaGdCNEUrUmhKZlNFMmZzRFFHSVpwV1dZMFVFMlBsOXFERjU5czBUZGlKdml2WHVPeFkxSGVHamF3UjhkaDdPd2hJQmtJVVc1MW9oY1BpVTNlMDlxK1o4aFFpd1VnM2ovVHhlM1RkdUovS0dXTzliZ3g5QUF3N2Vsa2dBQVZBaWZHNDlSY0NtRDBEQ3VFcFdBT1VKRVpDVWJMNnZieDA0RzMxNDdGSytrVGJGTmpVc0ZjNmU4MDNaNTZOREJCQUFEaFZHcUVtUlI2L3RoNjZzQUZ2WExhMFVZa1NBbmVKOGJkOVhkaUp1aUhYdDF4WTJmT0l0Qk1vaGdBQW9oenQzYlNKdzZOamxwaVA1Zjl5MmZsazBiSEZYeVB4VDNEMUdSRUIzUmxuUDBOYmVlejQ2bi9XeVRPZXZXcmtnMC9tY0RBRWJCQkFBTnFnU1oyWUNIMzVtWHdESTlQclNJeXNSQVpsTHAvTUZVMXlISzdYdDdlNlA3UXFBTlFpQXpwV3E1RytQVzE1cXVPVHN6ZHcrR0FGZ2M0cVA3UjN0WmtxcndtOWtBYkJaUmdrNnJRaUpDRWhvNVBkYU5SR2crbWh6V0Nxa0xXZnpxMld6WTdLNXV1dnNPMVgzVXpBQ3dPWnFjNHNYMk50b3FMcFZiMjdPZGgwNE0vZWdoU09JQUF0UVRaUlZGQUYyU0UyeHNyazJpYTEwRTIrMUNBUWpBS3BWYk5YTXpaNlBMeFNXTVVTQUhkUlZFZ0cyaDZYb3dkcXBnOFNhbmdBQ0lEMnJ0bWN5Qk5BV1RhWXZ0QmlMVGJOcmMySVFBYzFFOHZsY0ZSRmdlM3FxT0JGNkkyQmJQQjMveXU3aVpMM2xPdityZ3FtQk5wZU41SWVjWDhYYytVRXh3d0JKaWhFQkNZbDhYNnNnQWpROTFhWmZTaWlyemVWYnM2Wmk0NW1iRDlWZ0JJRE5IM0xWcHBibFU3VjZpOFcyODFXclZDRUNXbEhwLzFnVlJJQk4zNkgrQ1ljYmcyMi9xeE5ucndVQk55QUJNR210UUJrQ3lCZHQwVllBcFI0UmtHOFpKckg1TGdMWTZDc3BTYmRlc1FEa1V4N0JDQUNiUDJUYjQxSDVGTFUvc1pSaEJSQWRSSUNkT3VLN0NMQkRoVmo3SVhDYnhRVzliQTRYOTVObkc5Y0dJd0JzN2pqSGRKNzhxMllaVmdEbEFoR1FmMWtxUmw5RkFOWTlPL1doMzFpSGx3MzJHMFhiNjQ5YjNwMjA3WTFMK0NJWUFYRHVpajJ2VGpVYWlJQjhhNitzQUx2TXhpeGxCRVNBSGVvK2lnRFZCVnNobE5YbWJQQ3pXUzQyMHV0cW5NRUlBTnZUT2hZdnNLZElYYTA4dHRNbEswQlphaHdSWUtkMGZSSUJkN05oajUxS2tFT3NOaTBBMmdzaWxCQ01BRGhoZVczbk5jTnM3NW4zajBiVHNMYnYvaUx2YUZQSGh3aElqU3JUaWI2SUFOdnI5WWZVMEdTcUlDbE92bTJadmVldG5qdWhoR0FFZ0UwblFGVVc1dlRhK2Nsb0tPRFZmZU4ySWs4Ukt5SWdCYVFlVHZGQkJLeGZzNmlIbktXL3hPYlU1UFNwOE85TTFSMmJRNjVqcDY3NkI2WEhGQWNqQUtUcWJIcDNzcjFuanpVd3hXVnZHVitBUFpaM1ordVVERVJBSnpxOWYrZXlDRkNacjczRG5nQ3d2Y3BnNzZYaS9wV3JiN0hYKzFmdWJYY1dYU0ljakFDd1hiQzJ6WVV1VlpveTB2THFlK1BSNFpQbEtYTkVnSjFTZDFVRVBMeHVpWjBNVDhkcXN6TmlOZUVPUkc3N1dSdlMwRXhRQXVDSXhRYUU3VDN0UGhsa3dmbloyNmRLY3dwVTdoQUJkc3JZTlJHZ2N0Nnc1Z1k3bVoyTzlkQ3hTMWJqcjNMa3ExZmFzd0RvT1JQUzBFeFFBc0NtMlUwUHNlR2w5cVlNVmZrSG5UWnY2alU5OStZSlJFQmFZQm5PMjduL1RMVHpnNjh5WEpIdnFTNkpnSWZYTFkzRlhyNDVuQjNiNFlER21XZm52UDlQcXl3S2dES3RqUDJUeVI1RFVBTGdjOU9BMkF4cjcxaGdNM3JpTmdRUUFmYXFBU0pneXNxejlSNjc1bi8xTXVYY1N1aU5nTTBoQUpWTlNDRW9BWERFc3VwZXZSSUJVTVNQQnhGZ2ozTElJa0NtL3hjZldXa1A3blRNSDlMNDk4elk5dG9Nb1EzTkJDVUExSERZSE45QkFQVDh1ODU4SVNJZ003TFVGNFFvQWpRRThkYy91Tm02NlYrRjhQN1l4ZFJsd1ltekNkanMvZXRPb1EzTkJDVUE0Z0krYWMvMHR2YU9oVmJucDg3K0tmQUpFV0N2RG9Ra0F0VDRxK2R2dTNGSlNnc0xRRUlpKyt2ZDVobHJNNFEwQTBBY2d4TUFOaDBCQlJRcmdDZ1VGeEFCOWxpN0lBSmVlZlMyYUt0eHlyTVZaUFovNWRGYkMydjhkMzkwUGpwaDJSZkpGaXNYNHJVNUEwRExqdHUwRUx2QXJ6a040UW1BVTNiWGVWNzdOWHVMaHpRWEhwK25DQ0FDN05XRXNrV0FjdmJFeHFGb2RNdUthTmcwMW5rR0NRczEvaElCUllVeUY3UXFLbysyN3FQT2xjMnlDcTMzcjNJS1RnQWNPbVpYQU5nMlVkbjZjZmtlTHlMQVhnbTZJQUsyZkh0eDlKSXgwNnZSbHNtK255QkhzaGNmR1k2RlJiOXhaVW1IZXBoNC8yY2hOdnZjdFpZM1p3ck5BVkIwKy9zbHpTNGZMejZwb2JCcDVzRVBvTHhxZ0Fpd3g5NEZFYURlbjZ3QjZyWExJcERGSTF4cmRFZzhxT0YvNllmRFpwbGZ1MlBKclVxaXpIVVdXcVhIdDJPMjkyWUl6UUZRNVYrYzdjdWgydmFoV1lYTDVrcGZHMWJmWU5hdXYrQlFqc05KU2lJQzlLQXZhM1ZHTlZUcXJUNzc1c2xLamZkS0JDaHN1L2VtVWl1VStHNzV0djRXR3pFL0dTOFJMZlB0Q2RQRFBuZDVZaVp0T2svREJtcnM5YjdNb043L0hqUCtUK2lOZ0N3MU52ZG1VS3BDSEFJbzkxZlJXMTNvK3lxWjRXd0tnTHZOSmlJSWdMNkxxZWNJRUFFOW8rdDZvU3NpSUVub1ZNT3dzSlFlZlpLR05LLzAvdE5RYW45T0ZtdFArMWphZjZPWkdUWXR3KzN2WE80M3dRMEJDUGRoeTQ2QTkxbGVSN3pjS3VQSDNSTVJvSjVYV1NHeEJPVHR2RlpXZnBMN3VqQWNrS1RGaDFkNi8vMlhrdTFuYW9pOWY1VktrQUpBRmdDYmFrKzlFcHdCKy8vUjl4c0RJcUJmZ3UydlJ3UzBaOVA4emF2N1RqY2Y0bk5HQXJhZnA2RXV6aFNrQUZEZE8yeHhRU0RGTHo4QVF2a0VFQUgyeWdBUjBKMnQ1djN2UDh6S2Y5MUp0VDlENW4vYlBoeWh6czRJVmdEc1AyeDNPMDQ1S0MxZVdHdGZxL21tTUFLSUFIdW9FUUh0MmNyMHYvT0RLY2ZKOW1meFRUY0NlcGJhRENHdnpCaXNBTEJ0OHRFd0FLc0MydnpaWm9zYkVaQ05WNWF6RVFHdGFXM2YvVVdsWm9HMHpxWDlveHZXM0dqMUp2c1BoenRqSzFnQm9BWkJmelpEMmRPbGJPYk54N2dSQWZaS0RSRXdtNjE2L3FHYWxXZVQ2Ty9UNWdJc3FiOGJzMnNON28rQTNhdURGUURDK3I3bHNUa1dCYkpiZVh1SkhSSFFDN1YwMXlBQ3BqanB1Ykp6LzFmcG9IRldSd0tiNzdMYis5Y3dUY2g3TTRRdEFBcllsblBydW1VZEt6aGZGazhBRVdDUGVlZ2lRQTNLOXQxZjJnTWNVTXh5L0xPOStNLzdBWnYvVlpXQ0ZnQzJwd01LOE5aN2x1aUY0QmdCUklDOUFnbFZCS2p4Zi9iTkUvSHFoUGJvaGhQenRudnRkNTVzKzRLNVhscEJDd0FWenU2UHpsa3RJOVlFc0lxM3I4Z1JBWDNoNjNoeGFDSWdhZnhETmlkM3JCQTlmR2w3N3IvS0xIUS9qZUFGUUJFS0VHZkFIbjc5QlYyQ0NMQUhPaFFSUU9PZmZ4MlM4NS90dWYvYUV5YjBFTHdBS0dJWVFNNkF0dFZzNkJXNW4vd2pBdnFoMS9uYXFvc0FOU0kvL3VYblFUdVNkYTRCdlgxYmhQbi9yWU5uZTB0Y2hhNEtYZ0NvTEhjZHNMOVl4K2E3N0M1bVVhRTZXVXBXRUFIMnNFc0UvTjNicDh5MDIvTDJaYkNSdTEybUFYbnV6Wk9NK2VjTXQ0amV2K3JpMk1tck9hZmN2K2dRQUtiTTNpOWdIaWdyQTdyLzQwQUUyQ3NqTFlmN25IR1FxNElJMEQ0aXI1bjEvVjk3Yjl3ZXNJQmp0ajMxVDJqWm5YR3FnaUVBREFmdEJGWEVlQkJUQXFjcW5jdi9FUUgyU2tkc0gzLzkzN3grK09vNThkK055WC9YQWJ2T3cvWkt3ZTJZdGU2LzdhbC9JaER5OHIrTk5RQUJNRTNEOXQ0QXVvMm1CTEkvUUdQMWMvTTlJc0J1dVdoSTRQSFgvK1NWTlNEcDljdmtqNmUvdmZxeGJYMEJVLytNTllveW5DcERCTUIwWGQ3OThYbXJXd1RyTnBvU3lJeUFhZUNPdnlBQzdCWlFZZzNRb2ptdUR3dG9yUDh4WTdtZzEyKzNUaFRWKzk5am52V0VLUUlJZ09tYWNQN3lwUFUxQVhTcnJmY3NqWWJOQ2xjRTl3a2dBdXlYMFI2elhhNkdCVndUQXVyeGF5dmZ4NHlsUW1QOWVqNFE3Qko0NXZzcjdON0F4QzZ4YVhzSmVPdVp5UEVHZzk5OFlQU0ZIT1B6T3FvckUvWEk5dGFUQXFUNXJmditHTzRPVkQ1VkVqMzQ5NXNsbzdVajJSSmp3U2tqNkw0YjF0d1FPNnRXdFNHU1I3YW1aUjAvT3hFUGs5bWVBOTZ1SE5Ydy8rcjNaNklYMy9raS9vMVdsWGU3L0pkMVhKNy9SVHg3WDlzM2p2ZC9ReUhYSG56NTAzckQ1K0RmdnZqSXlrS2NVTFJrYU9pclVQbFUyZFFndmZqSXNCRnZnNlVsV3hhSlp3TVpneFp2aVo3L1lEYURXYk55Z1ZYbVU3MzljMFpnWGVRM2FaVjArOGhmZi94MjZ3di82TzZ5NkREK2Y3MGNFQURYV2NUdnRHRFBTK1pCYnp2SUMxWFRvZ2orRUVBRWxGTlc0cTdmNVZyakliNXE1ZnkrQllFYS9NTW5MMGVIUHJzY2U0TWp4TXNwMStTdVd2Um4yL3Fia28vV1hqV2tzNE9ObW1ieFJRRE13akgxNGU4ZnZjMDhaT2EzK0NiZlE5dC84MldFUTBxK1RHM0hoZ2l3VGJoNy9IS21YVzErbjNwZGM4djgyS2RHd3lUTk0yelUwSjh6UXpneTQ1OHdRd3VmR3d1S2hocm9BWFpuWE5RWitqMjk4dWl0Y1ZuYXZpZTkvN21FRVFCem1VUWFqM3BtaTMySEZEMlk1RjJzQnhYQkh3S0lBSC9LaXBTNlRVRFBXVDF2YlFkNi82MEpsK1BWMURvdHpoeVZsMmdSalRMVEFwMHA4a3dKWVhaQUpseWNESUdXQk5Ud0Y5SDQ2K2FzKzkreUNDSUVRQXN1NnBrWHNUK0FicTFwZ1d3VTFLSVFIRCtFQ0hDOGdFaWUwd1NtT2ovMkYvMFJCUGxic2U1LzYrcUFBR2pOSmRwMThGd2hWZ0RkWG1hdzV2SExOc25pc0VNRUVBRU9GUVpKOFlxQUZrVFRVRm9SZ1hYLzIxTkdBTFJoVTZRVlFEOEVWZ2hzVXhDT0gwWUVPRjVBSk04NUFscnhUOHVpRnhIVSsyZVdSM3ZTQ0lEMmJHSXJRRkhMbERJVTBLRWdIUDhLRWVCNEFaRThad2pJOUYvRWluOUpoclhDSktFOUFRUkFlemJ4OUtFaXpVY01CWFFvRE1lL1FnUTRYa0FrendrQ1Jacis1Zm5QbE0vT3hZNEE2TXduMGxybGg4M2M0U0tDaGdLZXVIOTVFYmZpSGhZSUlBSXNRQ1hLeWhDUXgzOVJwbjlCMi9uQm1jcXdzNVVSQkVBS3NxL3RPNTNpckh4TzBYcllXOWN0elNjeVlpbWNBQ0tnY09UYzBBTUM2dHc4ZWY5UVlTbWw5NThPTlFJZ0JTYzVrWHg0N0ZLS00vTTVSWHRpYTZVemdwOEVFQUYrbGh1cHRrZEFlNnhvL0wrSUlMOHRldi9wU0JkVEl1blM0dlJaTzNZWFp3WFFEK1g1SDl6QzFFQ25hMFRueENFQ092UGgyM0FJYUZpenFDbC9vdnJXd1RPTS9hZXNYZ2lBbEtEMFFOOTE0R3pLcy9zL1RUK1k1Myt3c3YrSWlLRTBBb2lBMHRCelkwY0lhS09mSXNmOTFmdmZkZUNjSTdsM1B4a0lnQXhsSkxOU1VkTUNsYXkxWmdlMEp3b2NOOHVBZ2xOVEVrQUVwQVRGYVpVam9QbitSZXp5MXdqdXAyK2ZiUHpJK3k0RUVBQmRBRFYrcmNXQlhpM1FJVkQzMXZvQU9BVTJsb0ovN3hFQi9wVVpLZTZQZ0N5WVJjNzNWMnJsK01lU3Y5bktEUUdRalZlMDMyd1VWS1JEb0pMM3hNWWg5Z3ZJV0U2dW5ZNEljSzFFU0k4dEFtcjg1ZlJYNUxnL2puKzlsU1lDb0FkdWNnZ3NZcmZBeHFUOWpYRUtaR1pBSXhILzNpTUMvQ3N6VXB5TmdCeVlpMjc4bFVJdDJNYWlQOW5LU21jakFMSXpNMzRBMTZML2JTcGNrU0daR1RCczFEWEJYd0tJQUgvTGpwUjNKNkRaUzBYMi9KVWltZjYxWUJzaE93RUVRSFptOFJWdm1Sa0JSUThGNklmMWtqR3RJUUo2TERSSExrTUVPRklRSkNOWEFscktYSTdMUlFaTS8vM1JSZ0Qwd2ErTW9RQkVRQjhGNXRDbGlBQ0hDb09rOUUxQWpiK1craTA2WVBydmp6Z0NvQTkrZW9nWFBTdEF5VVVFOUZGb0RsMktDSENvTUVoS3p3VEthdnd4L2ZkY1pETVhJZ0JtVVBUMlJtTlBSUzRRbEtRU0VaQ1E4UHNWRWVCMytZV2UrcklhZjB6LytkUThCRUFPSEl0ZUlDaEpNaUlnSWVIM0t5TEE3L0lMTmZWbE5mN2kvZXliSi9ENno2SGlJUUJ5Z0tnRmduNzY5cW5DcHdZcTZZaUFIQXJRZ1NnUUFRNFVBa2xJUldCcXF0OXdLV1ArU3FBNlhFejVTMVZVWFU5Q0FIUkZsTzZFc1pOWENwOGFtS1JNSXVCL1Bub3I2d1FrUUR4OWRVa0VzT2FFcDVYSWNyTDFyTkU4LzZLOS9aTnN2VzhXWXR1NXY5Z3AyTW05cS9nNitNMEhSbCtvWXNiS3lOTy9mSDRsM3ZMeVc3Y1hPeFZHZVYwd3J4WTlkUGVTMkFyeEI1TU9ncDhFWkUzYVAzWXgyckRteG1oSlFkdW5OcFBTZlRmKzJZM1J2NTYrRmgwemZ3UUlpRURTK0g5OVJUbGJsV3ZjLzhWM3Z6RFB1RWtLSkNjQ0NJQ2NRQ2JScVBIOXpwMDNSQ3NXRHlhSENuMzl6c2dpYzc5YWRPaXp5NFhlbDV2bFI4QUZFU0JCS1JGQVhjcXZYSDJPU1J2N3ZQVEQ0V2g1U2M4MXJidzYrZy9ITWYzblhJa1FBRGtEdlRwUmovN2ZKNWRLN2NISlBLZHh1ajk4ZmptNk9wRnpCb211RUFJdWlBQmxOS2xMdnpkMW1oQW1BVzFHOXR4RE44ZFd4cklJdlBMYjA5R2hZM1JxOHVhUEQwRGVSRTE4R3N1VlUyQ1pRYnNJdnZMb2Jhd2FXR1loOUhsdkYzd0NsQVhWcGI4M1BpYXNRTmxuZ1hwNCtSUDNMNDgzSXlzejZYTDZZNmxmT3lXQUJjQU8xK2owaFlubzNKVjY5TjA3WlpJdkoyZ3NkOE9hRzZMalp5Y1l5eTJuQ1BxK3F5dVdBQTFwcVM2Tm5icHF6TENZbGZvdVdNY2pTTWI3N3pObFhtYllkZkJzOU1ZLzRmUm5xd3dRQUxiSW1uamxGR2lHNDQwWnRWd1J3Rml1eFVJdUlHcFhSSUFFNVpaNHVWZDhUQW9vOXRKdXNkNDArai9iV3V4MnZxMHllL2prMWVobkpWdFNXNldyU3NjUUFKWkxVK05XR284dlkyWkFZOVkwbHF1MXV0OGZ1NFFYYlNNWVQ5NjdJZ0tFUzNWSmpjVHZQN2xNWGZLay9xUkpwcDVUai8rN29lakpqVU9sanZjcnJmTDRmLzZ0azlTdk5BWFh4emtJZ0Q3Z3BiMVVEbFNyVnk2SXlwbytrNlF6NmNGZE5UTzdtQ3FZVVBIbjFTVVJrQXdKeUR0Ynd3SUV2d25JeS8rbnB0Zi8zWGdXVWJsNVVlUFBTbi9GbEFFQ29Cak9wcmQwcWRUcGdVazJOYjFMVXdYdk5zTVNINXFwZ21wVUNQNFFjRWtFSkQ0bWNnNlVDS0F1K1ZPUGtwU3ExLytmdjdzc2V1YjdLMHBiZHlKSmkxNXAvQnRwMkgrUEFMRFBPTDZEcGdmdSsrTUZNN2Q2c1JNL05EbjVhRHdYYTBCQkZTREgyN2drQXBTdE5jYTZKUWRCckFFNUZuSUJVU1c5ZnBXZEMwSDFSMmIvWTZleEtCVlZIclVIWC82MFh0VE51RSt5bXRhd1dWV3JuSVdDV3BXQnBwczkrK1pKRnRsb0JjZmhZMU9lMm03VkpTMkovWGR2ZjBGZGNyamVxTmUvN2Q2YnpQVE9KYzZrVW8yL3pQNnFQNFRpQ0NBQWltTTljeWNYSDl4S25QYlhacU9ObVdMeTRnMTF5WXRpY2lhUld0Um4yL3Bsc1dPeUs0bWk4Uyt2SkJnQ0tJRzlheWJjQkVGaXlsMnljSkNsaEJNb2pyKzZYcGNZRm5DakFzbmMvL3hmM0JJUCs4a1B5SlZBNDE5dVNXQUJLSkcvcTcwM0lkR3d3TTc5WmdXdWo4K1hTSWhicHlWQVhVcExLcXp6MVBCdlczOVRhYnYzZGFKTjQ5K0pUakhmSVFDSzRkejJMbnB3Ly9VUGJqR09WT1hzc05VMllkTmZJQVM2RVhMbmU1ZEZnQ2hSbDRxckt5NDMvS0pBNDE5Y1hlaDBKd1JBSnpvRmZTZW5uQmNmR1haV0JBZ0REKytDS2tPZnQzRmRVRktYK2l6Z0xwZTczdkJQbFQvei9Mc1VZMkZmSXdBS1E5MzlScHFMdS9tdXhkMVBMUEdNUkFob0RZRVRacGlBNEI0QkNjcG50cXlJcCthNWw3cnJLYUl1WFdmUjd6c2ZHbjdsa1huKy9aWjB2dGNqQVBMbDJYZHM4dERWRkIwZkFyTUczQzZsSis0ZmluZnljenVWTWdkUFJyODdmSkVaS0JrTFNrSlBYdjJhenFmM3JnZXQ3YTlkVXVrNHVGTlNDQUIzeW1JbUpUNkpBQ1g2c0ptNys5YUJjL0hLZ3Z5NFo0clJpVGUrMWFVUHpkNFoydnIxL2JHTHJDellwZ2I1MHR0dlRMNmNpVjk5YjV3eWJZVGl3SHNFZ0FPRjBDb0pEbmw4Q0FBQUZhaEpSRUZVMm16bHI3YmNiSlM5TzFOMldxV3orWmlzQXUrYjN0eCs4d0FudUVIZzRYdVdSazhhYTRCdmdicDB2Y1RVNkd2RlBxM2U2VU52LzNyS285aXlzM00vVy9vMk1uSGxQUUxBbFpKb2tRN1h2YnBiSkhubVVHTFdsUmc0Rk5DZUE4Tkw1NW1ObithYjVaNm5oSnZHUEYxWUoxK2JVVDF2WnB1NHRBTGxUR1hwOGliVXV1UnpvNjhpbGFmL3pnL0dvMTNHT2tod2t3QUN3TTF5bVVtVlJJREdjbDFacjNzbVlSbmZ5TFM3MzRpQkQ0OWRxdFR1Y2VxTjZVR3RMWEk3OWM0UzAzYVo2eXI0TUVNZ1RiV3FjbDNhc0hwcW82NzdURy9mdDU1K1k5bEorR3E4bjZWOUc2bTQ5eDRCNEY2WnRFeVJiMk81TFRNeGZWRGUzMlBHSWVpUUVRVytDWUxHQnY5dTAraHI5Y1FzUVhsL2RkOTRMSWF5WEpmbnVWV3RTL0pGa2JYSmx5QnIwZG83RnNSYmhVdmdTNkJWSWVnM3ZYMzNhWno5UENoTUJJQUhoWlFrVVg0QlQ5Ni8zRXN6YnBLSGRxL3ExYW0zSUdFUUN3UUh0cGROelBtM21RZXpHbnVaOXZONlNHdVZ4WjBmbERjdXV0bU1KYXN1K2VaajBxNytOQjVQNnRJSjB3dVZLQ2g3Q0VhaThkYWxnM0g5MFZDTS9yVHdsODg5L0ViZWplOTNIVHdidldhYy9RaCtFRUFBK0ZGT002bFVBelM2WmJucE9TeWFPVmJWTnhyNzFkUWhDUUxOTHRDclRJczZmczZNTDU0NDIvczZCSHI0TGw0d0VJL1ZUNDNaRDBiRFpvZEc4WjM2Ykw2M1BMV3FiQkdndkdvQktoLzlBckxXK2FRdTZWVkNNNmxMaWtkMXFwKzZKS0dva1BoK2lPdXcrVnRpNmsrZW9qRytpYVAveEhENzdpOWlxNTZqU1NSWkxRZ2dBRnBBOGVGUWxjeTQvZkxXdzF6aG5IbTR5L0dvVldoczVQU0FkaVZvQzFRTmhaUVpxRXZYNlUrSnk4bVpBMnJZa3RCWWgzVE1wWHFVcExHTVYweitaVkRQNTU0SWdIdzRsaEpMU0QyNFVnQVhjRk9KbHgvLzh2TzJ3cVdBSk1TMzBCREhNMmJhYVhNalY5VDl1WTkvQlBEeTk2L01tbFBNZHNETlJEejZyTjdLVzJiTUxUSXp6a0lZRXZDb2FGSW5WV2JpcThhQVViYnptc2JMdFhiRGxOazZtMk5qNnN4eVltVUlxTmYvL0Z1bm90OS9jcWt5ZVFveEl3aUFDcFM2VE1oN1ByNWdwZ3JlR0QvQUs1Q2w0TEpRNXZUQUJMWUVwVmJnTzM1MkluWlVreGdnUUtDUmdIcjliL3h1UEhybC83S3FYeU1YWDk4akFId3R1YVowWXcxb0F1TFJSdzNsN1A3b1FuVCt5dld4NXpLVHI1a1lFaVFMQm12UnQyNWZXR1pTdUxkREJPajFPMVFZT1NVRkg0Q2NRTG9ValJxVUtpd2U1QkpUMjJuUm9pbGFOZEcxZ0orSmF5VlNmSHJrQ1BucXZ0T2xybDFSZks3RHVDTVdnQXFXczZ3QisvNTRBVk91UjJYN0wzKzZIUDNoOHl2T3BUaXhMREVzNEZ6UldFK1F6UDIvK3YwWk03M3Z5K2lJc1FvUnFrZkFuZmxRMVdOYmVvNjBxNXIrTk0xcjgxMUw4UEF1dlVUYUo4RDJtZ1B0NzV6dUc5VWorWnBzL3ZhTjNteFhuUzVubk5XS0FGUDdXbEdwM2pHR0FLcFhwaTF6SkZQdXRudU5FREFyd0JIY0k3RDlOMS9FanB6dXBXeHVpcWhMYzVsVTVZZ2EvcDBmbkNsOWJZcXE4SFE5SHdnQTEwc281L1R4OE00WmFFN1J1YkFnVU5hc1VKZXlFblAzZkJwK2Q4dkdac29RQURicE9odzNEMjkzQ2tmajdQL3BmMzNtVG9JeXBvUzZsQkdZUTZmVDhEdFVHQ1VrQlFGUUFuU1hic25EdS96UzJHM0cxM2NZUnl2ZkEzWEpueEtrNGZlbnJHeW1GQUZnazY1SGNTY1A3N3ZOSmtNc0IxdHN3VDMyK3A4cXRYVnFVcGZ3TnltMkhxVzVHdzEvR2tyaG5JTUFDS2VzVStWVTN1amFtM3pidlRjaEJGSVI2KytrcXZUK1cxR1FFTkFlQTlTbFZuU0tPNmJwZkxzL09oY3Y3cVJGbmdnUVNBZ2dBQklTdk00aG9JZjNscnNXTTNOZ0RwbDhEbWlCbGFtTmdOeFlBVENmWExXT1JkYUF6WGZkeUo0VnJmRllPYXJlL3Y2eFM2YnhQeDl2b1czbEprVHFOUUVFZ05mRlYwemlrNTdjdyt1V1JHdFdzbEZNSHRUVitNdnovOFQwVnNaNXhPbERIS3BMV2t1QWRTbnNsRmJTMjllZURtVnZNMjBuaDhTYUp3RUVRSjQwQTRocnRSRUFXNDBRd0ZlZzk4Sld6Mno3N3RQQk5mN054QklMRTNXcG1VeTJ6MnIwRDUrOEhNL2ZsNGxmczBvSUVFaERBQUdRaGhMbnRDU2dCL2lHMVRmRXV4RGlPTmdTMGF5RDZ2WHYvT0NyZUhYR1dWL3dJZllWMEhBVFlpQmRaVkNqTHlINS90Z0ZzNGZFSlJyOWROZzRxNGtBQXFBSkNCOTdJeURMd0ZvakNOYXZYc1E0YndQQzQ4YkVyMDErTU1rMlFPbnlWc0p5N2RjV3hxSmdyWm1WUXBnaTBGaVg2T2xUSy9JZ2dBRElneUp4ekNLZ21RU05EL0dRL0FiVU0vdmQ0UXZSa1ZOWHpldkY0TTM4c3lwR0R4L2tNN0JxNWZ6b1BtTnAwaXQxcVFlSVhBS0JOZ1FRQUczQWNEZy9BbzJDWUhYOEVGOFlMVjVZeSs4R0pjWjArT1NWMk5scTdOUVZZNUs5UW9OdnVTd1NRU0FMZ2VwU2xTd0U2dUYvYURaY092VFpKZXFTNVhwRTlGTUVFQURVaEZJSWFNaGdlTmxndE9hVytiRzFRQ0xCNWQ1ZDRtZ2wwK3VKc3hQeGcxb1BiQnl1U3FrK3MyNmExS1ZFRkt4WjZiYkFWRjM2L016VlNIVkpsaUtKU0V6NnM0cVVEd1VSUUFBVUJKcmJwQ09RUE14dk02YmY0YVdEY1M5UDR1QzJaZk90V2czMFVENTNlU0pTb3k1blBVM1BPMzcybW5rdzZ6TU5mYnJTYytjczFSbFpDR0poYVVSbTQyZnFranZsUkVyS0pZQUFLSmMvZDg5SVFBOXltWUdUSVlSazlzR1NoWVBSNGdYdGh4WE9YNWxxNEpQYnFaR2ZhdlFuTWRzblVBSjdIVGIxYUltcFQvM1VwWE5HT01vS1JGMEtyUEpVSkxzSWdJb1VKTm1BQUFRZ0FBRUlaQ0V3a09Wa3pvVUFCQ0FBQVFoQW9Cb0VFQURWS0VkeUFRRUlRQUFDRU1oRUFBR1FDUmNuUXdBQ0VJQUFCS3BCQUFGUWpYSWtGeENBQUFRZ0FJRk1CQkFBbVhCeE1nUWdBQUVJUUtBYUJCQUExU2hIY2dFQkNFQUFBaERJUkFBQmtBa1hKME1BQWhDQUFBU3FRUUFCVUkxeUpCY1FnQUFFSUFDQlRBUVFBSmx3Y1RJRUlBQUJDRUNnR2dRUUFOVW9SM0lCQVFoQUFBSVF5RVFBQVpBSkZ5ZERBQUlRZ0FBRXFrRUFBVkNOY2lRWEVJQUFCQ0FBZ1V3RUVBQ1pjSEV5QkNBQUFRaEFvQm9FRUFEVktFZHlBUUVJUUFBQ0VNaEVBQUdRQ1JjblF3QUNFSUFBQktwQkFBRlFqWElrRnhDQUFBUWdBSUZNQkJBQW1YQnhNZ1FnQUFFSVFLQWFCQkFBMVNoSGNnRUJDRUFBQWhESVJBQUJrQWtYSjBNQUFoQ0FBQVNxUVVBQzRHZzFza0l1SUFBQkNFQUFBaEJJU2VBb0ZvQ1VwRGdOQWhDQUFBUWdVQ1VDQ0lBcWxTWjVnUUFFSUFBQkNLUWlVQjluQ0NBVktFNkNBQVFnQUFFSVZJZEFQYXJGQW1DOE9sa2lKeENBQUFRZ0FBRUlkQ1ZRcjM4MVVEZi91cDdJQ1JDQUFBUWdBQUVJVklkQXJYYWFJWURxRkNjNWdRQUVJQUFCQ0tRalVLOS9NbENMSW9ZQTB1SGlMQWhBQUFJUWdFQWxDS2p0SDZoSGt3aUFTaFFubVlBQUJDQUFBUWlrSXpBNU1PL0l3RUEwLzJDNjB6a0xBaENBQUFRZ0FJRXFFS2hOWHZscVlESzZpZ1dnQ3FWSkhpQUFBUWhBQUFJcENWeUtvb05tR0NDS0huejUwOVBtWlNqbGRad0dBUWhBQUFJUWdJREhCTjU1K2h1MVpDVkFyQUFlRnlSSmh3QUVJQUFCQ0tRblVJK0gvbU1CWU5ZQ2VDLzloWndKQVFoQUFBSVFnSUN2Qk9yMTZCT2xQUllBbWc3Z2EwWklOd1FnQUFFSVFBQUNtUWcwV0FDaXlmaERwc3M1R1FJUWdBQUVJQUFCN3dqVUIrWWRVS0pqQ3dCVEFiMHJQeElNQVFoQUFBSVE2SW5BNU9TVmVBakFXUCtuQWpNQkVoSzhRZ0FDRUlBQUJLcExRRE1BbEx0a0ZvQjVXenVxQXdRSVFBQUNFSUFBQktwSm9CNUZlNU9jWFJjQTljbjNrb084UWdBQ0VJQUFCQ0JRUFFLMWV2MmZrMXpOQ0FDekp3Q09nQWtWWGlFQUFRaEFBQUlWSkZBZkdJd2RBSlcxR1FGZzN1L1ZBUUlFSUFBQkNFQUFBdFVrTURGNWRjWUNNT01FcUt6aUNGak5BaWRYRUlBQUJDQUFBVU5nM0RnQUxrOUlORm9BZEd4djhnV3ZFSUFBQkNBQUFRaFVoMER6cXIrekJRQkxBbGVucE1rSkJDQUFBUWhBb0lHQU1mbnZiZmc0eXdjZ3FrV0RzNzVzUEpIM0VJQUFCQ0FBQVFqNFMrQmFOTG0zTWZXemZBRDBCWDRBalhoNER3RUlRQUFDRUtnRWdhTm0vSDlWWTA1bUR3R1liOHdZd1Z1TkovQWVBaENBQUFRZ0FBRy9DVFF1QUpUa1pJNEFpSnBNQk1tSnZFSUFBaENBQUFRZzRDZUJlbTF3VjNQSzV3aUFSVkUwNTZUbWkvZ01BUWhBQUFJUWdJQS9CQzVQWG5tdk9iVnpCTUN1MFZYajVxUzl6U2Z5R1FJUWdBQUVJQUFCL3dqSS9MOTNxbTJmbGZnNUFpRCtGaitBV1pENEFBRUlRQUFDRVBDV1FHM2dqVlpwYnlrQUZrYVRMVTl1RlFISElBQUJDRUFBQWhCd21NRGsxVG5tZjZXMnBRQmdHTURoZ2lScEVJQUFCQ0FBZ1pRRVpQNS9kM1RWMFZhbnR4UUE4WWtNQTdUaXhURUlRQUFDRUlDQVB3VGFtUCtWZ2JZQ1lIb1lRQTZCQkFoQUFBSVFnQUFFZkNUUXh2eXZyTFFWQU5QREFBZDl6QzlwaGdBRUlBQUJDSVJPd0pqLzMyaG4vaGVidGdKQVg5YnJFei9SS3dFQ0VJQUFCQ0FBQWM4STFDZCswU25GdFU1ZjZqdjJCdWhHaU84aEFBRUlRQUFDemhHWXMvWi9jd283V2dCMHN0a2I0T2ZORi9FWkFoQ0FBQVFnQUFGM0NkUnJBeTkwUzExWEFiQW9tbnk1V3lSOER3RUlRQUFDRUlDQVF3UTZPUDhscWV3cUFLYWRBWGNsRi9BS0FRaEFBQUlRZ0lDN0JMbzUveVVwN3lvQWRLSnhCbVFZSUNIR0t3UWdBQUVJUU1CaEFoTXAyK3hVQXNCTUk5aHI4cW8vQWdRZ0FBRUlRQUFDamhMUXluKzdSMWVsbXNLZlNnQW9uMHdKZExTMFNSWUVJQUFCQ0VBZ0lkQmg1Yi9rbE9TMTZ6VEE1RVM5bWltQnZ6VXZtL1NlQUFFSVFBQUNFSUNBVXdTNlR2MXJURzFxQzRBdU1sYUFqb3NLTkViTWV3aEFBQUlRZ0FBRWlpT1FadXBmWTJveVdRQjBvYkVDSERFdkkzcFBnQUFFSUFBQkNFREFDUUtaZXY5S2NTWUxRSnpGK3NTb0Uxa2xFUkNBQUFRZ0FBRUl4QVN5OXY1MVVXWUxnQzdDRjBBVUNCQ0FBQVFnQUFFbkNHVHUvU3ZWMlMwQTVpSm1CRGhSNENRQ0FoQ0FBQVFnRVBYUyt4ZTJuaXdBdWhBcmdDZ1FJQUFCQ0VBQUF1VVIwTHovZDUvK3h2ZDZTVUZQRmdEZENDdEFMN2k1QmdJUWdBQUVJSkFqZ2ZyRTQ3M0cxck1BaUZjSFpLZkFYcmx6SFFRZ0FBRUlRS0F2QXFiMy80WnBpNC8yR2tuUEFrQTNYQmhOdm1CZXh2V2VBQUVJUUFBQ0VJQkFZUVNPUnZXSm4vUnp0NzRFUUx4VFlMM2VWd0w2U1R6WFFnQUNFSUFBQklJa1lDencvZlQreGF5V0J6Z2NBdk9nU0J3UWdBQUVJQUNCVkFSNm12YlhISE5mRm9Ba01od0NFeEs4UWdBQ0VJQUFCT3dTTUczdTkvSzRReTRDQUlmQVBJcUNPQ0FBQVFoQUFBS2RDZFROc0h1L3B2L2tEcmtJQUVVMjdSQjROSW1ZVndoQUFBSVFnQUFFY2lWdzlOM1JPMS9JSzhiY0JJQWNBbzFaNHZHOEVrWThFSUFBQkNBQUFRaGNKNUNYNlQrSk1UY0JvQWdaQ2tpdzhnb0JDRUFBQWhESWowQ2VwdjhrVmJuTUFrZ2lTMTRmZlBtVEEyYUN3YnJrTTY4UWdBQUVJQUFCQ1BSTUlCZXYvK2E3NTJvQlNDS3YxeWYvMHJ4bmdhQUVDSzhRZ0FBRUlBQ0IzZ2hvZUQwWHIvL20yMXNSQUxHSElnc0VOYlBtTXdRZ0FBRUlRQ0FiZ1J5OS9wdHZiR1VJSUxuSmd6cytlVG1xMVo1S1B2TUtBUWhBQUFJUWdFQktBbWExdjNkRzczdzY1ZG1aVDdOaUFVaFN3ZFRBaEFTdkVJQUFCQ0FBZ1V3RWpsNmMybThuMDBWWlRyWXFBS2FuQm1yc0FuK0FMS1hDdVJDQUFBUWdFREtCb3hyMzMydW0xOXVFWUZVQUtPSHlCekFaa1ZNZ0FRSVFnQUFFSUFDQmJnVHFFNk41cmZiWDZWYldCWUJ1UHIwK3dHaW5oUEFkQkNBQUFRaEFJSFFDbXUvL3p1aXFYVVZ3c09vRTJKd0JuQUtiaWZBWkFoQ0FBQVFnTUUzQXN0TmZNK2RDQllCdXp0YkJ6VVhBWndoQUFBSVFDSjFBUFlyMnZ2djBOK1F6VjFnb1pBaWdNVGNMWTMrQStzSEdZN3lIQUFRZ0FBRUlCRXpnNktVU2ZPVUtGd0JUTXdQaWxRS1BCbHpZWkIwQ0VJQUFCQ0FnQW9WNC9MZENYZmdRUUpLSUIzWWNHYW5WQm45clBvOGt4M2lGQUFRZ0FBRUlCRVFnYnZ5TDhQaHZ4YlEwQWFERUlBSmFGUW5ISUFBQkNFQWdBQUtsTnY3aVc2b0FVQUttUllEWlBUQWEwbWNDQkNBQUFRaEFvT0lFdE1IUFBXWDEvQk8yaGZzQUpEZE9YZ1dnTnJYVGtkVVZqNUw3OFFvQkNFQUFBaEFva2NDNDJyeXlHMy9sdjNRTFFGSUlEQWNrSkhpRkFBUWdBSUdLRWlqZDdOL0kxUmtCb0VRaEFocUxodmNRZ0FBRUlGQWhBazQxL3VMcWxBQlFnaEFCb2tDQUFBUWdBSUVLRVhDdThSZmIwbjBBbWd0WTR5TGFCY2tjUDlyOEhaOGhBQUVJUUFBQ2ZoR29IMVNiNXNLWWZ6TTM1d1NBRWloUVpzWEFlOHpidmZwTWdBQUVJQUFCQ1BoR1FNdjdYcXhQT3RuNGk2VnpRd0ROQmN3R1FzMUUrQXdCQ0VBQUFzNFRLSGhqbjE1NE9Ha0JhTXpJTzZOM1BxM3RFUnVQOFI0Q0VJQUFCQ0RnTElGNmZWUnRsN1BwbTA2WTh4YUFCT0NETzQ1c2pXcURPOHpua2VRWXJ4Q0FBQVFnQUFHSENHaUJuNzgwdzloN0hVcFQyNlI0SXdDVUEyWUl0QzFIdm9BQUJDQUFnVklKeU5sdlVvMy8wVktUa2VIbXpnOEJOT1pseGpuUWpLMDBIdWM5QkNBQUFRaEFvRFFDcGsxeTJkbXZIUmV2TEFDTm1URE9nVTlIdGRyZm1tUHNJZEFJaHZjUWdBQUVJRkFVZ2ZISStLaVo4ZjZYaTdwaG52ZnhWZ0FJQWtNQ2VWWUY0b0lBQkNBQWdmUUUvRFA1TitmTmF3R1FaT2FCSForOFVKdXlCaVNIZUlVQUJDQUFBUWpZSWVEQkZMODBHYStFQUZCR3NRYWtLVzdPZ1FBRUlBQ0JQZ2hvcGRySGZmSHk3NWJQeWdpQUpLTllBeElTdkVJQUFoQ0FRRzRFNU9nWFRiNndkM1JWWmJhdXI1d0FVR0ZqRGNpdHloTVJCQ0FBZ2FBSmFEbmZxRDd4azZyMCtoc0xzNUlDSU1uZzlFeUJwOHpua2VRWXJ4Q0FBQVFnQUlFVUJMejI4RStSUC9mM0FraVRpVTdueUJwZ05qMlVrK0NQT3AzSGR4Q0FBQVFnQUlHWVFBWE4vYTFLdHRJV2dNWU14MEtnTnZpNnlmQ214dU84aHdBRUlBQUJDSWhBbGMzOXJVbzRHQUdRWk40SWdjZHF0VUV0SURTU0hPTVZBaENBQUFUQ0pSQmF3NStVZEhBQ0lNazRRaUFod1NzRUlBQ0JZQWtjTlE1K1p1ZStWYnRDSkJDc0FFZ0tHeUdRa09BVkFoQ0FRQmdFNGg1L2JlQ05kNSs2NHhkaDVMaDFMb01YQUFrV0NRR3ozZkNQREpCTnlURmVJUUFCQ0VDZ09nUkNOZlczSzBFRVFCT1poM1ljV1RjWkRUek5ySUVtTUh5RUFBUWc0QytCWFdZRnY1OVhjUzUvUDBXQ0FHaER6MWdFUm1yUndGYXo0eURyQ0xSaHhHRUlRQUFDRGhNWXI1dnBmSmVpeVplcnRIcGZucndSQUNsb0dqR3d5YXdsWUdZUHNKWkFDbHljQWdFSVFLQXNBbHFtZHkrOS9YVDRFUURwT01WbnlTcGczbXpDVnlBRE5FNkZBQVFnWUptQXh2WUhhclZkRnlhdi9ZTGVmbnJZQ0lEMHJHYWRpUmlZaFlNUEVJQUFCQW9sUUtQZlAyNEVRUDhNbzAwN2pnemRZQ3dEZGVNellJWUpOcG9vUjNLSWxpZ2dBQUVJUU9BNmdYSFQ2Tzh5OC9iZnUyUmU2ZWxmQjlQck93UkFyK1E2WEtlWkJOSEF2STNHQVdXVE9VMS9RK2FQQUFFSVFBQUM2UW5FNC9tbVU3VTNtcnoyM3E5SFZ4MU1meWxucGlHQUFFaERxYzl6cHFZV1J1dk1ySUoxOVZydHp3MzBUWDFHeWVVUWdBQUVxa1RBTlBaMXN5cGY5RjQ5bWxSRHY5ZE0yVHRhcFF5Nm1CY0VRRW1sSWxGZ3pGa2padGhnblZHNGYyNHEvMGdVMWRhVmxCeHVDd0VJUUtBSUFqTGptOFkrMmx1cjE3K2lzUzhDZWZ0N0lBRGFzeW5sRy9rVExJcGtMWWlHYWdQejdweWNuRnh1MWlLNFU1L05EMmVvRnRYTmEyMUlpVFBIUmtwSkpEZUZBQVFnTUUzQVBKZU9Oc0E0YXA1TFU0MTh2ZjdKd01EQTZjbkphMThOUk5IQlNYT2NYbjBES1FmZS9uOUdlQTB1TE0yalZRQUFBQUJKUlU1RXJrSmdnZz09Ii8+CjwvZGVmcz4KPC9zdmc+Cg==",title:(0,D.__)("Zoho CRM","formgent"),subtitle:(0,D.__)("Send FormGent form submissions directly to Zoho CRM.","formgent"),showToggle:"1"===e?.zoho?.connected,switchChecked:"1"===e?.zoho?.status,onToggleChange:M=>async function(M){t(M?"1":"0","status");try{await N({zoho:{...e?.zoho,status:M?"1":"0"}});const t=M?(0,D.__)("Zoho is enabled","formgent"):(0,D.__)("Zoho is disabled","formgent");(0,n.doAction)("formgent-toast",{message:t,type:"success"})}catch(M){console.error(M)}}(M),switchLoading:!1,content:(0,E.jsx)(A,{handleUpdateZohoSettings:t,handleUpdateSettings:async function(){try{await N({zoho:{...e?.zoho},triggeredFrom:"zoho-integration"})}catch(M){console.error(M)}}})})})}var O=e(2736),L=e.n(O);function k(M){const{Fill:N,Slot:e}=(0,t.createSlotFill)(M),g=({render:M})=>(0,E.jsx)(N,{children:N=>(0,E.jsx)(M,{...N})});return g.Slot=e,g.propTypes={render:L().elementType.isRequired},g}const V=k("CPTSettingsGlobal");function F(){const M=(0,j.LB)();return(0,E.jsx)(E.Fragment,{children:M&&(0,E.jsx)(V.Slot,{children:M=>(0,E.jsx)(E.Fragment,{children:M})})})}function p(){const{updateSettings:M}=(0,g.dispatch)(T.A.GLOBAL_SETTINGS),{googleMapApiKey:N}=(0,g.useSelect)(M=>{const{getGoogleMapApiKey:N}=M(T.A.GLOBAL_SETTINGS);return{googleMapApiKey:N()}},[]),e=()=>{const[e,t]=(0,c.useState)(N);return(0,E.jsx)("div",{className:"formgent-zapier-form-fields",children:(0,E.jsxs)("div",{className:"formgent-zapier-form-field",children:[(0,E.jsx)("span",{children:(0,D.__)("Api Key","formgent")}),(0,E.jsx)("div",{className:"formgent-zapier-form-field-input",children:(0,E.jsx)(I.AntInputPassword,{className:"formgent-form-field",value:e,onChange:M=>{t(M.target.value)}})}),(0,E.jsx)(I.AntButton,{type:"primary",className:"formgent-save-settings-btn formgent-mt-20",htmlType:"button",onClick:async function(){await M({google_map_api_key:e}),(0,n.doAction)("formgent-toast",{message:(0,D.__)("Google map api key saved","formgent"),type:"success"})},children:(0,D.__)("Save Key","formgent")})]})})};return(0,E.jsx)(x,{className:"formgent-zapier-integration",children:(0,E.jsx)(I.Collapse,{icon:"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNSIgaGVpZ2h0PSIyMCIgdmlld0JveD0iMCAwIDE1IDIwIj4KICA8ZyBpZD0iZ29vZ2xlLW1hcC1tYXJrZXIiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC02My41KSI+CiAgICA8cGF0aCBpZD0iUGF0aF8xNTk0IiBkYXRhLW5hbWU9IlBhdGggMTU5NCIgZD0iTTIwNC43NzMsOTMuNjExLDE5Niw5NS45NDlsMi4zMzgsMTMuOTYzLDUuOTg5LTcuOTMzYTcuNSw3LjUsMCwwLDAsLjQ0Ny04LjM2OFoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC0xMjcuMzM4IC04OS45MTIpIiBmaWxsPSIjMGY5ZDU4Ii8+CiAgICA8cGF0aCBpZD0iUGF0aF8xNTk1IiBkYXRhLW5hbWU9IlBhdGggMTU5NSIgZD0iTTE4MS4wMTYsMzE2LjkzOGwyLjkyMSwzLjg3di03Ljc1MVoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC0xMTIuOTM3IC0zMDAuODA4KSIgZmlsbD0iIzMxYWE1MiIvPgogICAgPHBhdGggaWQ9IlBhdGhfMTU5NiIgZGF0YS1uYW1lPSJQYXRoIDE1OTYiIGQ9Ik0xOTguMzM4LDE5Ny4ybDIuMjU3LTMtMi4yNTctMS43TDE5NiwxOTQuODQ4WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTEyNy4zMzggLTE4NC45NzQpIiBmaWxsPSIjZjY5NDExIi8+CiAgICA8cGF0aCBpZD0iUGF0aF8xNTk3IiBkYXRhLW5hbWU9IlBhdGggMTU5NyIgZD0iTTE5OC4zMzgsMCwxOTYsMi41N2wyLjMzOCwyLjU3TDIwMC4yNi4yNDlBNy41MSw3LjUxLDAsMCwwLDE5OC4zMzgsMFoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC0xMjcuMzM4KSIgZmlsbD0iIzQxNzVkZiIvPgogICAgPHBhdGggaWQ9IlBhdGhfMTU5OCIgZGF0YS1uYW1lPSJQYXRoIDE1OTgiIGQ9Ik0xMDIuMiwyLjk4OVY3LjVoNS45OTJWMEE3LjQ4OCw3LjQ4OCwwLDAsMCwxMDIuMiwyLjk4OVoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC0zNy4xOSkiIGZpbGw9IiM0MDg2ZjQiLz4KICAgIDxwYXRoIGlkPSJQYXRoXzE1OTkiIGRhdGEtbmFtZT0iUGF0aCAxNTk5IiBkPSJNNjUuMDA4LDc2LjczYTcuNSw3LjUsMCwwLDAtLjQ0Myw4LjM2M2w1LjM0Ny00LjY3MloiIHRyYW5zZm9ybT0idHJhbnNsYXRlKDAgLTczLjc0MSkiIGZpbGw9IiNlYjQxMzIiLz4KICAgIDxwYXRoIGlkPSJQYXRoXzE2MDAiIGRhdGEtbmFtZT0iUGF0aCAxNjAwIiBkPSJNMTg4LjI0Nyw2LjM5M2wtMS45MjIsMi41NTQtMi44MjUsNi40aDUuMDgybDQuMTc5LTUuNTUyYTcuNTE0LDcuNTE0LDAsMCwwLTQuNTEzLTMuNFoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC0xMTUuMzI1IC02LjE0NCkiIGZpbGw9IiM0MDg2ZjQiLz4KICAgIDxwYXRoIGlkPSJQYXRoXzE2MDEiIGRhdGEtbmFtZT0iUGF0aCAxNjAxIiBkPSJNMjAwLjMyOSw3MS45MzlsLTIuMjU3LDMsMi4yNTcsMS43WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTEyOS4zMjkgLTY5LjEzNikiIGZpbGw9IiM2MDlhZjYiLz4KICAgIDxwYXRoIGlkPSJQYXRoXzE2MDIiIGRhdGEtbmFtZT0iUGF0aCAxNjAyIiBkPSJNMTk2LDE5Mi41bDIuMzM4LDQuNywyLjI1Ny0zWiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTEyNy4zMzggLTE4NC45NzQpIiBmaWxsPSIjZjhhODA4Ii8+CiAgICA8cGF0aCBpZD0iUGF0aF8xNjAzIiBkYXRhLW5hbWU9IlBhdGggMTYwMyIgZD0iTTk1LDE0OC45bC00LjE3OSw1LjU1MWE3LjUwOSw3LjUwOSwwLDAsMCwuNDQ2LjY2MmwzLjA2OCw0LjA2MywyLjkyMS0zLjg4MXYtNC43WiIgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoLTI2LjI1OCAtMTQzLjA2NSkiIGZpbGw9IiNmYmJkMDAiLz4KICAgIDxwYXRoIGlkPSJQYXRoXzE2MDQiIGRhdGEtbmFtZT0iUGF0aCAxNjA0IiBkPSJNMjIxLjE2MiwxMjBsLTEuNDEyLDIuODI1LDEuNDEyLDIuODI1YTIuODI1LDIuODI1LDAsMCwwLDAtNS42NDlaIiB0cmFuc2Zvcm09InRyYW5zbGF0ZSgtMTUwLjE2MiAtMTE1LjMwOCkiIGZpbGw9IiNlM2U3ZWEiLz4KICAgIDxwYXRoIGlkPSJQYXRoXzE2MDUiIGRhdGEtbmFtZT0iUGF0aCAxNjA1IiBkPSJNMTgzLjUsMTIyLjgyNWEyLjgyNSwyLjgyNSwwLDAsMCwyLjgyNSwyLjgyNVYxMjBBMi44MjUsMi44MjUsMCwwLDAsMTgzLjUsMTIyLjgyNVoiIHRyYW5zZm9ybT0idHJhbnNsYXRlKC0xMTUuMzI1IC0xMTUuMzA4KSIgZmlsbD0iI2ZmZiIvPgogIDwvZz4KPC9zdmc+Cg==",title:(0,D.__)("Google Map","formgent"),subtitle:(0,D.__)("Integrate Google map with FormGent","formgent"),content:(0,E.jsx)(e,{})})})}const h=[{key:"1",label:"Settings",disabled:!0},{label:(0,E.jsxs)("span",{className:"dropdown-header-content",children:[(0,E.jsx)(s.A,{src:d}),(0,D.__)("Disconnect integration","formgent")]}),key:"disconnect-mailchimp"}];function Z(){const M=(0,c.useRef)(null),[N,e]=(0,c.useState)(!1),[i,j]=(0,c.useState)(!1),[a,z]=(0,c.useState)(null),[r,x]=(0,c.useState)(!1),{updateMailChimpStatus:u,updateSettings:y}=(0,g.dispatch)(T.A.GLOBAL_SETTINGS),{settings:Q}=(0,g.useSelect)(M=>{const{getSettings:N}=M(T.A.GLOBAL_SETTINGS);return{settings:N()}},[]),m=(0,c.useMemo)(()=>(0,l.addQueryArgs)("https://login.mailchimp.com/oauth2/authorize",{response_type:"code",client_id:"819668269349",redirect_uri:"https://app.formgent.com/wp-json/formgent-mailchimp/access-token"}),[]);async function A(M,N=!1){u(M);try{await y({mailchimp:{...N?{}:Q.mailchimp,status:M?"1":"0"}});const e=N?(0,D.__)("Mailchimp Disconnected","formgent"):M?(0,D.__)("Mailchimp Enabled","formgent"):(0,D.__)("Mailchimp Disabled","formgent");(0,n.doAction)("formgent-toast",{message:e,type:"success"}),x(!1)}catch(M){console.error(M)}}return(0,c.useEffect)(()=>{const t=setInterval(()=>{M.current&&M.current.closed&&(clearInterval(t),e(!1),N&&!i&&z("Connection failed or was cancelled"))},500);return()=>clearInterval(t)},[N,i]),(0,c.useEffect)(()=>{const N=async N=>{if("https://app.formgent.com"===N.origin){if("mailchimp"!==N.data.key)return;if("oauth-success"===N.data.type){j(!0),e(!1),z(null);const{updateSettings:t,invalidateResolution:n}=(0,g.dispatch)(T.A.GLOBAL_SETTINGS);await t({mailchimp:{status:!0,data:N.data.data}}),M.current&&!M.current.closed&&M.current.close()}else"oauth-error"===N.data.type&&(j(!1),e(!1),z(N.data.message||"Connection failed"))}};return window.addEventListener("message",N),()=>{window.removeEventListener("message",N)}},[]),(0,E.jsxs)("div",{className:"formgent-card-action formgent-mt-20",children:[(0,E.jsx)("div",{className:"formgent-card-action-icon",children:(0,E.jsx)(s.A,{src:"data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjgiIGhlaWdodD0iMjgiIHZpZXdCb3g9IjAgMCAyOCAyOCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGcgY2xpcC1wYXRoPSJ1cmwoI2NsaXAwXzg5MTJfMTA4MjIpIj4KPHBhdGggZD0iTTEzLjE0NSAxLjkxNjY4ZS0wNUM3LjkyMjk3IC0wLjAxNzQ4MDggLTIuMTIzMiAxMS45NTM3IDEuNjI5OTcgMTUuMTI0N0wyLjU1MTYzIDE1LjkwNTJDMi4zMDI4NyAxNi41NzIyIDIuMjE0OTQgMTcuMjg4NSAyLjI5NDk3IDE3Ljk5NThDMi4zOTI5NyAxOC45NzU4IDIuODk5MyAxOS45MTM5IDMuNzE4MyAyMC42Mzk1QzQuNDk1MyAyMS4zMjc5IDUuNTE3MyAyMS43NjQyIDYuNTA4OTcgMjEuNzY0MkM4LjE0OTMgMjUuNTQ0MiAxMS44OTkgMjcuODYzNSAxNi4yOTI2IDI3Ljk5NDJDMjEuMDA2IDI4LjEzNDIgMjQuOTY0NSAyNS45MjIyIDI2LjYyMjMgMjEuOTQ4NUMyNi43MzA4IDIxLjY2ODUgMjcuMTkxNiAyMC40MTMyIDI3LjE5MTYgMTkuMzAzN0MyNy4xOTE2IDE4LjE4ODQgMjYuNTYxNiAxNy43MjY0IDI2LjE1OTEgMTcuNzI2NEMyNi4xNDc1IDE3LjY4MzIgMjYuMDY4MSAxNy4zOTI3IDI1Ljk1ODUgMTcuMDQyN0MyNS44NSAxNi42OTI3IDI1LjczNjggMTYuNDQ3NyAyNS43MzY4IDE2LjQ0NzdDMjYuMTc0MyAxNS43OTA4IDI2LjE4MjUgMTUuMjA1MiAyNi4xMjQxIDE0Ljg3MjdDMjYuMDYyMyAxNC40NjA5IDI1Ljg5MDggMTQuMTEwOSAyNS41NDU1IDEzLjc0OEMyNS4yMDAxIDEzLjM4NTIgMjQuNDkzMSAxMy4wMTMgMjMuNTAwMyAxMi43MzU0TDIyLjk4IDEyLjU5MDdDMjIuOTc3NiAxMi41Njg1IDIyLjk1MiAxMS4zNjIyIDIyLjkyOTggMTAuODQ0MkMyMi45MTM1IDEwLjQ3MDkgMjIuODgwOCA5Ljg4NTE4IDIyLjcgOS4zMTAwMkMyMi40ODMgOC41MzA2OCAyMi4xMDczIDcuODQ4MTggMjEuNjM3MSA3LjQxMTg1QzIyLjkzNDUgNi4wNjc4NSAyMy43NDQxIDQuNTg2MTkgMjMuNzQxOCAzLjMxNTY5QzIzLjczODMgMC44NzE1MTkgMjAuNzM2NSAwLjEzMTg1MyAxNy4wMzgxIDEuNjYzNjlMMTYuMjU0MSAxLjk5NjE5QzE1Ljc3NTYgMS41MjYxOSAxNS4yOTY1IDEuMDU2OCAxNC44MTY4IDAuNTg4MDE5QzE0LjM1NDggMC4xODU1MTkgMTMuNzg2NiAwLjAwMjM1MjUgMTMuMTQ1IDEuOTE2NjhlLTA1Wk0xMy4yMzAxIDEuMDE4NTJDMTMuNDIzOCAxLjAxODUyIDEzLjYwNTggMS4wNDA2OSAxMy43NzI2IDEuMDg2MTlDMTQuMTE5MSAxLjE4NDE5IDE1LjI2NiAyLjUxNDE5IDE1LjI2NiAyLjUxNDE5QzE1LjI2NiAyLjUxNDE5IDEzLjEzNTYgMy42OTYwMiAxMS4xNTkzIDUuMzQ0NTJDOC40OTkzIDcuMzk0MzUgNi40ODY4IDEwLjM3NCA1LjI4MjggMTMuNjA2OUM0LjMzNjYzIDEzLjc5MTIgMy41MDI0NyAxNC4zMjc5IDIuOTkyNjMgMTUuMDY4N0MyLjY4ODEzIDE0LjgxNDQgMi4xMTk5NyAxNC4zMjIgMi4wMTk2MyAxNC4xMzA3QzEuMjA1MyAxMi41ODM3IDIuOTA3NDcgOS41NzgzNSA0LjA5NzQ3IDcuODgwODVDNi44MDY0NyA0LjAxMzM1IDEwLjkzMTggMS4wMTE1MiAxMy4yMzAxIDEuMDE4NTJaTTE3LjA2MzggNC44MzcwMkMxNy4xMTA1IDQuODM0NjggMTcuMTMzOCA0Ljg5NTM1IDE3LjA5NjUgNC45MjMzNUMxNi45Mjk2IDUuMDUxNjggMTYuNzQ3NiA1LjIyNjY4IDE2LjYxNDYgNS40MDYzNUMxNi42MDk1IDUuNDEzMjEgMTYuNjA2MyA1LjQyMTM0IDE2LjYwNTUgNS40Mjk4N0MxNi42MDQ3IDUuNDM4NCAxNi42MDYyIDUuNDQ3IDE2LjYxIDUuNDU0NzFDMTYuNjEzNyA1LjQ2MjQyIDE2LjYxOTUgNS40Njg5NiAxNi42MjY3IDUuNDczNkMxNi42MzM5IDUuNDc4MjQgMTYuNjQyMiA1LjQ4MDgxIDE2LjY1MDggNS40ODEwMkMxNy40MTk2IDUuNDg1NjggMTguNTAyMyA1Ljc1NTE4IDE5LjIwODEgNi4xNTA2OEMxOS4yNTYgNi4xNzc1MiAxOS4yMjIxIDYuMjcwODUgMTkuMTY4NSA2LjI1ODAyQzE4LjEwMSA2LjAxMzAyIDE2LjM1MjEgNS44Mjc1MiAxNC41MzY4IDYuMjY5NjhDMTIuOTE1MSA2LjY2NjM1IDExLjY3ODUgNy4yNzY1MiAxMC43NzU1IDcuOTMzMzVDMTAuNzI4OCA3Ljk2NjAyIDEwLjY3NTEgNy45MDY1MiAxMC43MTEzIDcuODYzMzVDMTEuNzU2NiA2LjY1NTg1IDEzLjA0MzUgNS42MDU4NSAxNC4xOTYxIDUuMDE2NjlDMTQuMjM1OCA0Ljk5NTY5IDE0LjI3NzggNS4wMzg4NSAxNC4yNTY4IDUuMDc3MzVDMTQuMTY0NiA1LjI0NDE4IDEzLjk4ODUgNS41OTg4NSAxMy45MzI1IDUuODY4MzVDMTMuOTI0MyA1LjkwOTE4IDEzLjk2OTggNS45NDE4NSAxNC4wMDQ4IDUuOTE3MzVDMTQuNzIyMyA1LjQyNzM1IDE1Ljk2OTUgNC45MDQ2OSAxNy4wNjM4IDQuODM3MDJaTTIwLjU5MDYgOC41NzYxOEwyMC42NTYgOC41NzczNUMyMC44NDU1IDguNTg0ODIgMjEuMDI5NCA4LjY0MzcgMjEuMTg4IDguNzQ3NjhDMjEuODExIDkuMTYxODUgMjEuODk5NiAxMC4xNjY0IDIxLjkzMjMgMTAuOTAwMkMyMS45NDk4IDExLjMyMDIgMjIuMDAxMSAxMi4zMzQgMjIuMDE4NiAxMi42MjQ1QzIyLjA1ODMgMTMuMjkwNyAyMi4yMzMzIDEzLjM4NCAyMi41ODY4IDEzLjUwMDdDMjIuNzg1MSAxMy41NjcyIDIyLjk3MTggMTMuNjE1IDIzLjI0MzYgMTMuNjkyQzI0LjA2NzMgMTMuOTIzIDI0LjU1NjEgMTQuMTU4NyAyNC44NjUzIDE0LjQ1OTdDMjUuMDQ4NSAxNC42NDg3IDI1LjEzMzYgMTQuODQ4MiAyNS4xNjA1IDE1LjAzOTVDMjUuMjU3MyAxNS43NDg4IDI0LjYwOTggMTYuNjI2MiAyMi44OTQ4IDE3LjQyMDdDMjEuMDIgMTguMjkxIDE4Ljc0NSAxOC41MTE1IDE3LjE3MzUgMTguMzM2NUwxNi42MjQgMTguMjc0N0MxNS4zNjYzIDE4LjEwNTUgMTQuNjQ4OCAxOS43Mjk1IDE1LjQwMzYgMjAuODQyNUMxNS44OTAxIDIxLjU2IDE3LjIxNDMgMjIuMDI2NyAxOC41Mzk2IDIyLjAyNjdDMjEuNTc3NiAyMi4wMjY3IDIzLjkxMjEgMjAuNzMwNSAyNC43ODEzIDE5LjYwOTRDMjQuODA2NCAxOS41Nzc1IDI0LjgyOTcgMTkuNTQ0NCAyNC44NTEzIDE5LjUxMDJDMjQuODkzMyAxOS40NDYgMjQuODU4MyAxOS40MTEgMjQuODA0NiAxOS40NDcyQzI0LjA5NTMgMTkuOTMyNSAyMC45NDMgMjEuODYxIDE3LjU3MTMgMjEuMjhDMTcuNTcxMyAyMS4yOCAxNy4xNjE4IDIxLjIxMzUgMTYuNzg3MyAyMS4wNjc3QzE2LjQ4OTggMjAuOTUxIDE1Ljg2OCAyMC42NjYzIDE1Ljc5MjEgMjAuMDI4MkMxOC41MTQgMjAuODY4MiAyMC4yMjY2IDIwLjA3MzcgMjAuMjI2NiAyMC4wNzM3QzIwLjI0MjUgMjAuMDY2NSAyMC4yNTU3IDIwLjA1NDUgMjAuMjY0NSAyMC4wMzk1QzIwLjI3MzMgMjAuMDI0NCAyMC4yNzcyIDIwLjAwNyAyMC4yNzU2IDE5Ljk4OTdDMjAuMjc0NiAxOS45Nzk0IDIwLjI3MTYgMTkuOTY5NCAyMC4yNjY3IDE5Ljk2MDNDMjAuMjYxOCAxOS45NTEyIDIwLjI1NTEgMTkuOTQzMiAyMC4yNDcxIDE5LjkzNjdDMjAuMjM5IDE5LjkzMDIgMjAuMjI5OCAxOS45MjUzIDIwLjIxOTkgMTkuOTIyNEMyMC4yMSAxOS45MTk1IDIwLjE5OTYgMTkuOTE4NiAyMC4xODkzIDE5LjkxOTdDMjAuMTg5MyAxOS45MTk3IDE3Ljk1OTggMjAuMjQ5OCAxNS44NTE2IDE5LjQ3ODdDMTYuMDgxNSAxOC43MzIgMTYuNjkxNiAxOS4wMDI3IDE3LjYxMzMgMTkuMDc2MkMxOS4wNDczIDE5LjE2MTIgMjAuNDg1NCAxOS4wMDU4IDIxLjg2ODEgMTguNjE2NUMyMi44MjI1IDE4LjM0MzUgMjQuMDc1NSAxNy44MDM0IDI1LjA0OTYgMTcuMDM0NUMyNS4zNzc1IDE3Ljc1NTUgMjUuNDkzIDE4LjU1IDI1LjQ5MyAxOC41NUMyNS40OTMgMTguNTUgMjUuNzQ4NSAxOC41MDMzIDI1Ljk1OTYgMTguNjM1MkMyNi4xNjE1IDE4Ljc1ODkgMjYuMzA4NSAxOS4wMTU1IDI2LjIwODEgMTkuNjc5M0MyNi4wMDI4IDIwLjkxOTUgMjUuNDc1NSAyMS45MjYzIDI0LjU5IDIyLjg1MjdDMjQuMDM2MyAyMy40NTEzIDIzLjM3NzUgMjMuOTQzMiAyMi42NDYzIDI0LjMwNEMyMi4yNDk2IDI0LjUxNCAyMS44MjUgMjQuNjkzNyAyMS4zNzgxIDI0Ljg0MDdDMTguMDM4IDI1LjkzMTUgMTQuNjE4NSAyNC43MzIyIDEzLjUxNiAyMi4xNTc0QzEzLjQyNjcgMjEuOTYwNCAxMy4zNTI5IDIxLjc1NjggMTMuMjk1NSAyMS41NDgzQzEyLjgyNTMgMTkuODUwOCAxMy4yMjU1IDE3LjgxNSAxNC40NzE1IDE2LjUzMjlDMTQuNTQ3MyAxNi40NTEyIDE0LjYyNTUgMTYuMzU0MyAxNC42MjU1IDE2LjIzNDJDMTQuNjI1NSAxNi4xMzI3IDE0LjU2MTMgMTYuMDI1NCAxNC41MDY1IDE1Ljk1MDdDMTQuMDcwMSAxNS4zMTcyIDEyLjU1OTMgMTQuMjQwNCAxMi44NjI2IDEyLjE1NDRDMTMuMDgwOCAxMC42NTY0IDE0LjM5MSA5LjYwMDUyIDE1LjYxMjUgOS42NjM1MkMxNS43MTYzIDkuNjY4MTggMTUuODE5IDkuNjc1MTggMTUuOTIyOCA5LjY4MTAyQzE2LjQ1MTMgOS43MTI1MiAxNi45MTQ1IDkuNzgwMTggMTcuMzQ5NiA5Ljc5NzY4QzE4LjA3ODggOS44MzAzNSAxOC43MzQ1IDkuNzI0MTggMTkuNTExNSA5LjA3NjY4QzE5Ljc3NCA4Ljg1ODUyIDE5Ljk4NCA4LjY2ODM1IDIwLjMzOTggOC42MDg4NUMyMC4zNzI1IDguNjAzMDIgMjAuNDQ3MSA4LjU3NjE4IDIwLjU5MDYgOC41NzYxOFpNMjAuNjE2MyAxMS4xMTk1QzIwLjU5MjkgMTEuMTE5OCAyMC41Njk1IDExLjEyMTcgMjAuNTQ2MyAxMS4xMjU0QzIwLjE1NTUgMTEuMTg4NCAyMC4xNDE1IDExLjY3MTQgMjAuMjgwMyAxMi4zMzg3QzIwLjM1OTYgMTIuNzEyIDIwLjQ5ODUgMTMuMDMyOSAyMC42NTM2IDEzLjIzMTJDMjAuODU3OCAxMy4yMDc5IDIxLjA1MzggMTMuMjA1NSAyMS4yMzQ2IDEzLjIzMTJDMjEuMzM4NSAxMi45OTIgMjEuMzU2IDEyLjU4MTQgMjEuMjYyNiAxMi4xMzIyQzIxLjEzMiAxMS41MDggMjAuOTU4MSAxMS4xMTQ5IDIwLjYxNjMgMTEuMTE5NVpNMTYuMzQ2MyAxMi45MjMyQzE1LjkyMDkgMTIuOTIxNCAxNS41MDU5IDEzLjA1NDYgMTUuMTYxIDEzLjMwMzVDMTQuOTc0MyAxMy40NCAxNC43OTgxIDEzLjYzMDIgMTQuODIyNiAxMy43NDQ1QzE0LjgzMiAxMy43ODE5IDE0Ljg1ODggMTMuODA5OCAxNC45MjUzIDEzLjgxOEMxNS4wNzgxIDEzLjgzNTUgMTUuNjE2IDEzLjU2NDggMTYuMjM0MyAxMy41MjY0QzE2LjY3MDYgMTMuNDk5NSAxNy4wMzIzIDEzLjYzNiAxNy4zMTExIDEzLjc1OTdDMTcuNTkgMTMuODgxIDE3Ljc2MTUgMTMuOTYxNSAxNy44MjggMTMuODkxNUMxNy44NzExIDEzLjg0NzIgMTcuODU4MyAxMy43NjMyIDE3Ljc5MTggMTMuNjUzNUMxNy42NTQxIDEzLjQyOTUgMTcuMzcxOCAxMy4yMDIgMTcuMDcwOCAxMy4wNzM3QzE2Ljg0MTggMTIuOTc1NyAxNi41OTU0IDEyLjkyNDUgMTYuMzQ2MyAxMi45MjMyWk0yMS4xMDg2IDEzLjg2ODJDMjAuOTA5MSAxMy44NjQ3IDIwLjc0MzUgMTQuMDg1MiAyMC43Mzg4IDE0LjM1ODJDMjAuNzM0MSAxNC42MzM1IDIwLjg5MTYgMTQuODU5OSAyMS4wOTIzIDE0Ljg2MjJDMjEuMjkzIDE0Ljg2NTcgMjEuNDU4NiAxNC42NDY0IDIxLjQ2MzMgMTQuMzcyMkMyMS40NjggMTQuMDk2OSAyMS4zMDkzIDEzLjg3MTcgMjEuMTA4NiAxMy44NjgyWk0xNi45MzIgMTQuMDY4OUMxNi44NzM2IDE0LjA2ODkgMTYuODEzIDE0LjA3MTIgMTYuNzUxMSAxNC4wNzgyQzE2LjM4ODMgMTQuMTM2NSAxNi4xODc2IDE0LjI1NTUgMTYuMDU5MyAxNC4zNjY0QzE1Ljk0OTYgMTQuNDYyIDE1Ljg4MiAxNC41NjgyIDE1Ljg4MiAxNC42NDI4QzE1Ljg4MTggMTQuNjU0NCAxNS44ODQgMTQuNjY1OSAxNS44ODgzIDE0LjY3NjdDMTUuODkyNiAxNC42ODc0IDE1Ljg5OSAxNC42OTcyIDE1LjkwNzIgMTQuNzA1NUMxNS45MTUzIDE0LjcxMzcgMTUuOTI1IDE0LjcyMDMgMTUuOTM1NyAxNC43MjQ3QzE1Ljk0NjQgMTQuNzI5MiAxNS45NTc5IDE0LjczMTUgMTUuOTY5NSAxNC43MzE1QzE2LjA1MTEgMTQuNzMxNSAxNi4yMzU1IDE0LjY1OCAxNi4yMzU1IDE0LjY1OEMxNi42MDc4IDE0LjUxNzcgMTcuMDEwMSAxNC40NzU5IDE3LjQwMzMgMTQuNTM2N0MxNy41ODY1IDE0LjU1NzcgMTcuNjcxNiAxNC41NjgyIDE3LjcxMjUgMTQuNTA2NEMxNy43MjQxIDE0LjQ4NzcgMTcuNzM4MSAxNC40NDkyIDE3LjcwMDggMTQuMzg5N0MxNy42MjczIDE0LjI2OTUgMTcuMzM5MSAxNC4wNzU5IDE2LjkzMiAxNC4wNjg5Wk0xOS41Njg2IDE0LjUzNTVDMTkuNDIwNSAxNC41MzU1IDE5LjI5NDUgMTQuNTk1IDE5LjIzODUgMTQuNzA4MkMxOS4xNTEgMTQuODg3OSAxOS4yNzkzIDE1LjEzMTcgMTkuNTI1NSAxNS4yNTE5QzE5Ljc3MDUgMTUuMzczMiAyMC4wNDIzIDE1LjMyNTMgMjAuMTMyMSAxNS4xNDY4QzIwLjIxOTYgMTQuOTY2IDIwLjA5MTMgMTQuNzIyMiAxOS44NDUxIDE0LjYwMkMxOS43NTkzIDE0LjU1ODcgMTkuNjY0NyAxNC41MzY4IDE5LjU2ODYgMTQuNTM1NVpNNS45OTQ0NyAxNC41NjM1QzYuMDUwNDcgMTQuNTYzNSA2LjEwODggMTQuNTYzNSA2LjE2ODMgMTQuNTY3QzcuMDE5OTcgMTQuNjEzNyA4LjI3NTMgMTUuMjY3IDguNTYyMyAxNy4xMjJDOC44MTU0NyAxOC43NjcgOC40MTI5NyAyMC40Mzg5IDYuODcxOCAyMC43MDI1QzYuNzI4MyAyMC43MjU5IDYuNTgyNDcgMjAuNzM2NCA2LjQzNTQ3IDIwLjczMjlDNS4wMTIxMyAyMC42OTQ0IDMuNDczMyAxOS40MTIyIDMuMzIwNDcgMTcuODkyQzMuMTUxMyAxNi4yMTIgNC4wMDk5NyAxNC45MTkzIDUuNTMwMTMgMTQuNjEyNUM1LjY2NjYzIDE0LjU4NDUgNS44MjQxMyAxNC41NjU5IDUuOTk0NDcgMTQuNTYzNVpNNS45MTI4IDE1LjY0NUM1LjcyNzY0IDE1LjY0MzQgNS41NDQxNyAxNS42ODAzIDUuMzc0MDUgMTUuNzUzNEM1LjIwMzkyIDE1LjgyNjUgNS4wNTA4OCAxNS45MzQyIDQuOTI0NjMgMTYuMDY5N0M0LjQ4MTMgMTYuNTU3NCA0LjQxMjQ3IDE3LjIyMjMgNC40OTc2MyAxNy40NThDNC41MjkxMyAxNy41NDMyIDQuNTc5MyAxNy41Njc3IDQuNjE0MyAxNy41NzIzQzQuNjg4OTcgMTcuNTgxNyA0LjgwMDk3IDE3LjUyNjggNC44NzA5NyAxNy4zMzlDNC44NzgwNCAxNy4zMTg5IDQuODg0NjUgMTcuMjk4NyA0Ljg5MDggMTcuMjc4NEM0LjkzMzIzIDE3LjEyNzMgNC45OTQ3OCAxNi45ODIyIDUuMDczOTcgMTYuODQ2N0M1LjEzMTggMTYuNzU4MiA1LjIwNjUyIDE2LjY4MiA1LjI5Mzg0IDE2LjYyMjRDNS4zODExNyAxNi41NjI4IDUuNDc5MzkgMTYuNTIxIDUuNTgyODkgMTYuNDk5NUM1LjY4NjM4IDE2LjQ3NzkgNS43OTMxMSAxNi40NzcgNS44OTY5NyAxNi40OTY3QzYuMDAwODMgMTYuNTE2NSA2LjA5OTc3IDE2LjU1NjUgNi4xODgxMyAxNi42MTQ1QzYuNDk4NDcgMTYuODE3NSA2LjYxODYzIDE3LjE5NzggNi40ODU2MyAxNy41NTk1QzYuNDE3OTcgMTcuNzQ3MyA2LjMwNTk3IDE4LjEwNjcgNi4zMzA0NyAxOC40MDA3QzYuMzgwNjMgMTguOTk2OCA2Ljc0Njk3IDE5LjIzNzIgNy4wNzcxMyAxOS4yNjE3QzcuMzk2OCAxOS4yNzM0IDcuNjIwOCAxOS4wOTQ5IDcuNjc3OTcgMTguOTYzQzcuNzExOCAxOC44ODQ4IDcuNjgzOCAxOC44MzgyIDcuNjY1MTMgMTguODE3MkM3LjYxNDk3IDE4Ljc1NTQgNy41MzMzIDE4Ljc3NCA3LjQ1NTEzIDE4Ljc5MjdDNy4zOTQyNSAxOC44MDkxIDcuMzMxNTMgMTguODE3NyA3LjI2ODQ3IDE4LjgxODNDNy4yMDE0NyAxOC44MjAyIDcuMTM1MDYgMTguODA1NCA3LjA3NTE5IDE4Ljc3NTNDNy4wMTUzMyAxOC43NDUxIDYuOTYzODggMTguNzAwNiA2LjkyNTQ3IDE4LjY0NTdDNi44MzQ0NyAxOC41MDU3IDYuODQwMyAxOC4yOTU3IDYuOTQwNjMgMTguMDU3N0M2Ljk1MzQ3IDE4LjAyNSA2Ljk2OTggMTcuOTkgNi45ODczIDE3Ljk1MDNDNy4xNDgzIDE3LjU5MSA3LjQxNjYzIDE2Ljk4NzkgNy4xMTU2MyAxNi40MTM5QzYuODg4MTMgMTUuOTgyMiA2LjUxNzEzIDE1LjcxMTUgNi4wNzI2MyAxNS42NTU1QzYuMDE5MjUgMTUuNjQ4NCA1Ljk2NTQ5IDE1LjY0NDUgNS45MTE2MyAxNS42NDM5TDUuOTEyOCAxNS42NDVaIiBmaWxsPSJibGFjayIvPgo8L2c+CjxkZWZzPgo8Y2xpcFBhdGggaWQ9ImNsaXAwXzg5MTJfMTA4MjIiPgo8cmVjdCB3aWR0aD0iMjgiIGhlaWdodD0iMjgiIGZpbGw9IndoaXRlIi8+CjwvY2xpcFBhdGg+CjwvZGVmcz4KPC9zdmc+Cg==",width:42,height:42})}),(0,E.jsxs)("div",{className:"formgent-card-action-content",children:[(0,E.jsx)("h4",{className:"formgent-card-action-title",children:(0,D.__)("Mailchimp","formgent")}),(0,E.jsx)("p",{className:"formgent-card-action-description",children:(0,D.__)("Auto-sync FormGent forms with Mailchimp audience.","formgent")}),Q?.mailchimp?.data?.access_token&&(0,E.jsx)(I.AntBadge,{className:"formgent-badge-status",status:"success",text:(0,D.__)("Connected","formgent")})]}),(0,E.jsx)("div",{className:"formgent-card-action-button-wrapper",children:Q?.mailchimp?.data?.access_token?(0,E.jsxs)("div",{className:"formgent-card-action-group",children:[(0,E.jsx)(I.AntSwitch,{className:"formgent-card-action-switch",checked:"1"===Q?.mailchimp?.status,onChange:function(M){A(M)}}),(0,E.jsx)(I.AntDropdown,{className:"formgent-card-action-dropdown",overlayClassName:"formgent-dropdown-has-header",menu:{items:h,onClick:function({key:M}){"disconnect-mailchimp"===M&&x(!0)}},trigger:["click"],placement:"bottomRight",overlayStyle:{minWidth:"282px"},children:(0,E.jsx)("a",{onClick:M=>M.preventDefault(),children:(0,E.jsx)(s.A,{src:U})})}),r&&(0,E.jsx)(o.A,{title:(0,E.jsx)(E.Fragment,{children:(0,E.jsx)("span",{className:"formgent-popup-title-icon",children:(0,E.jsx)(s.A,{src:d})})}),onCancel:function(){x(!1)},onSubmit:function(){A(!1,!0)},hasCancelButton:!0,hasSubmitButton:!0,isActiveSubmit:!0,submitText:(0,D.__)("Yes. Disconnect","formgent"),className:"formgent-integration-disconnect-alert",children:(0,E.jsxs)("div",{className:"formgent-modal-content",children:[(0,E.jsx)("h3",{children:(0,D.__)("Are you sure to disconnect this integration?","formgent")}),(0,E.jsx)("p",{children:(0,D.__)("Data sync will be stopped for this integration. You can reconnect it anytime.","formgent")})]})})]}):(0,E.jsx)(I.AntButton,{className:"formgent-card-action-button",type:"primary",onClick:()=>{j(!1),function(N){e(!0),z(null);const t=void 0!==window.screenLeft?window.screenLeft:window.screenX,g=void 0!==window.screenTop?window.screenTop:window.screenY,n=window.innerWidth?window.innerWidth:document.documentElement.clientWidth?document.documentElement.clientWidth:screen.width,D=window.innerHeight?window.innerHeight:document.documentElement.clientHeight?document.documentElement.clientHeight:screen.height,i=n/window.screen.availWidth,T=`width=${500/i}, height=${500/i}, top=${(D-500)/2/i+g}, left=${(n-500)/2/i+t}`,j=window.open(N,"",T);window.focus&&j.focus(),M.current=j}(m)},children:N?(0,E.jsxs)(E.Fragment,{children:[(0,E.jsx)(t.Spinner,{}),(0,D.__)("Connecting...","formgent")]}):(0,D.__)("Connect","formgent")})})]})}const R=({email:M,handleConnect:N,isConnecting:e,sheetStatus:g,handleSheetStatus:n,handleDisconnectSheet:i,disconnectSheetModal:T,setDisconnectSheetModal:j})=>{const a=[{key:"1",label:"Settings",disabled:!0},{label:(0,E.jsxs)("span",{className:"dropdown-header-content",children:[(0,E.jsx)(s.A,{src:d}),(0,D.__)("Disconnect integration","formgent")]}),key:"disconnect-sheet"}];return(0,E.jsxs)("div",{className:"formgent-settings-content",children:[(0,E.jsx)("div",{className:"formgent-settings-content__head",children:(0,E.jsx)("h4",{className:"formgent-settings-content__title",children:(0,D.__)("Integrations","formgent")})}),(0,E.jsx)("div",{className:"formgent-settings-content__item",children:null===M?(0,E.jsx)(I.AntSkeleton,{active:!0}):(0,E.jsxs)(E.Fragment,{children:[(0,E.jsxs)("div",{className:"formgent-card-action",children:[(0,E.jsx)("div",{className:"formgent-card-action-icon",children:(0,E.jsx)(s.A,{src:"data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzIiIGhlaWdodD0iNDMiIHZpZXdCb3g9IjAgMCAzMiA0MyIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTE0LjgwNjMgMjIuMDk5Nkg4Ljg0MjI5VjE4Ljc1ODlIMTQuODA5OFYyMi4xMDE0TDE0LjgwNjMgMjIuMDk5NlpNMjAuNzczOCAwLjE0NTg3NFYxMC42NDU5SDMxLjI3MzhMMjAuNzczOCAwLjE0NTg3NFpNMjMuMTU5IDE4Ljc1ODlIMTcuMTkxNVYyMi4xMDE0SDIzLjE1OVYxOC43NTg5Wk0yMy4xNTkgMjQuNDg2NkgxNy4xOTE1VjI3LjgyOTFIMjMuMTU5VjI0LjQ4NjZaTTMxLjI3MiAxMS41MjA5VjM5LjI4MjlDMzEuMjcyIDQwLjg2NDkgMjkuOTkxIDQyLjE0NTkgMjguNDA5IDQyLjE0NTlIMy41OTA1NEMzLjIxNDU2IDQyLjE0NTkgMi44NDIyNyA0Mi4wNzE4IDIuNDk0OTIgNDEuOTI3OUMyLjE0NzU2IDQxLjc4NDEgMS44MzE5NSA0MS41NzMyIDEuNTY2MDkgNDEuMzA3M0MxLjAyOTE4IDQwLjc3MDQgMC43Mjc1MzkgNDAuMDQyMiAwLjcyNzUzOSAzOS4yODI5VjMuMDA4ODdDMC43Mjc1MzkgMS40MjY4NyAyLjAwODU0IDAuMTQ1ODc0IDMuNTkwNTQgMC4xNDU4NzRIMTkuODk3VjExLjUyMDlIMzEuMjcyWk0yNS41NDQzIDE2LjM3MzZINi40NTM1NFYzMC4yMTQ0SDI1LjU0NlYxNi4zNzE5TDI1LjU0NDMgMTYuMzczNlpNMTQuODA2MyAyNC40ODY2SDguODQyMjlWMjcuODI5MUgxNC44MDk4VjI0LjQ4NjZIMTQuODA2M1oiIGZpbGw9IiMzNEE4NTMiLz4KPC9zdmc+Cg==",width:42,height:42})}),(0,E.jsxs)("div",{className:"formgent-card-action-content",children:[(0,E.jsx)("h4",{className:"formgent-card-action-title",children:(0,D.__)("Google Sheets","formgent")}),(0,E.jsx)("p",{className:"formgent-card-action-description",children:M||(0,D.__)("Send your data insights to Google Sheets. Automatically syncs as results come in.","formgent")}),M&&(0,E.jsx)(I.AntBadge,{className:"formgent-badge-status",status:"success",text:(0,D.__)("Connected","formgent")})]}),(0,E.jsx)("div",{className:"formgent-card-action-button-wrapper",children:M?(0,E.jsxs)("div",{className:"formgent-card-action-group",children:[(0,E.jsx)(I.AntSwitch,{className:"formgent-card-action-switch",checked:"1"===g,onChange:n}),(0,E.jsx)(I.AntDropdown,{className:"formgent-card-action-dropdown",overlayClassName:"formgent-dropdown-has-header",menu:{items:a,onClick:function({key:M}){"disconnect-sheet"===M&&j(!0)}},trigger:["click"],placement:"bottomRight",overlayStyle:{minWidth:"282px"},children:(0,E.jsx)("a",{onClick:M=>M.preventDefault(),children:(0,E.jsx)(s.A,{src:U})})}),T&&(0,E.jsx)(o.A,{title:(0,E.jsx)(E.Fragment,{children:(0,E.jsx)("span",{className:"formgent-popup-title-icon",children:(0,E.jsx)(s.A,{src:d})})}),onCancel:function(){j(!1)},onSubmit:i,hasCancelButton:!0,hasSubmitButton:!0,isActiveSubmit:!0,submitText:(0,D.__)("Yes. Disconnect","formgent"),className:"formgent-integration-disconnect-alert",children:(0,E.jsxs)("div",{className:"formgent-modal-content",children:[(0,E.jsx)("h3",{children:(0,D.__)("Are you sure to disconnect this integration?","formgent")}),(0,E.jsx)("p",{children:(0,D.__)("Data sync will be stopped for this integration. You can reconnect it anytime.","formgent")})]})})]}):(0,E.jsx)(I.AntButton,{className:"formgent-card-action-button",type:"primary",onClick:N,children:e?(0,E.jsxs)(E.Fragment,{children:[(0,E.jsx)(t.Spinner,{}),(0,D.__)("Connecting...","formgent")]}):(0,D.__)("Connect","formgent")})})]}),(0,E.jsx)(Z,{}),(0,E.jsx)(S,{}),(0,E.jsx)(m,{}),(0,E.jsx)(p,{}),(0,E.jsx)(F,{})]})})]})};function Y(){const M=(0,c.useRef)(null),[N,e]=(0,c.useState)(!1),[t,i]=(0,c.useState)(!1),[j,a]=(0,c.useState)(null),[I,o]=(0,c.useState)(!1),{email:U,settings:d,sheetStatus:z}=(0,g.useSelect)(M=>{const{getGoogleSheetEmail:N,getSettings:e,getGoogleSheetStatus:t}=M(T.A.GLOBAL_SETTINGS);return{email:N(),settings:e(),sheetStatus:t()}},[]),{updateGoogleSheetStatus:s,updateSettings:r,setGoogleSheetEmail:x,googleDisconnect:u}=(0,g.dispatch)(T.A.GLOBAL_SETTINGS);async function y(M,N=!1){N&&x(!1),N&&await u(),s(M);try{await r({google_sheet:{...N?{}:d.google_sheet,status:M?"1":"0"}});const e=N?(0,D.__)("Google Sheet Disconnected","formgent"):M?(0,D.__)("Google Sheet Enabled","formgent"):(0,D.__)("Google Sheet Disabled","formgent");(0,n.doAction)("formgent-toast",{message:e,type:"success"}),o(!1)}catch(M){console.error(M)}}(0,c.useEffect)(()=>{const g=setInterval(()=>{M.current&&M.current.closed&&(clearInterval(g),e(!1),N&&!t&&a("Connection failed or was cancelled"))},500);return()=>clearInterval(g)},[N,t]),(0,c.useEffect)(()=>{const N=async N=>{if("https://app.formgent.com"===N.origin){if("google-sheet"!==N.data.key)return;if("oauth-success"===N.data.type){i(!0),e(!1),a(null);const{updateSettings:t,invalidateResolution:n}=(0,g.dispatch)(T.A.GLOBAL_SETTINGS);await t({google_sheet:{status:!0,data:N.data.data}}),n("getGoogleSheetEmail"),M.current&&!M.current.closed&&M.current.close()}else"oauth-error"===N.data.type&&(i(!1),e(!1),a(N.data.message||"Connection failed"))}};return window.addEventListener("message",N),()=>{window.removeEventListener("message",N)}},[]);const Q=(0,l.addQueryArgs)("https://accounts.google.com/o/oauth2/v2/auth",{client_id:"123104365122-hg8vtu8r7u4scc0gs9l2ibt5f8bgs26l.apps.googleusercontent.com",scope:"https://www.googleapis.com/auth/userinfo.email https://www.googleapis.com/auth/drive.file",redirect_uri:"https://app.formgent.com/wp-json/formgent-google-sheet/access-token",prompt:"consent",access_type:"offline",response_type:"code"});return(0,E.jsxs)("div",{className:"formgent-google-sheet-connection",children:[(0,E.jsx)(R,{email:U,handleConnect:()=>{i(!1),function(N){e(!0),a(null);const t=void 0!==window.screenLeft?window.screenLeft:window.screenX,g=void 0!==window.screenTop?window.screenTop:window.screenY,n=window.innerWidth?window.innerWidth:document.documentElement.clientWidth?document.documentElement.clientWidth:screen.width,D=window.innerHeight?window.innerHeight:document.documentElement.clientHeight?document.documentElement.clientHeight:screen.height,i=n/window.screen.availWidth,T=`width=${500/i}, height=${500/i}, top=${(D-500)/2/i+g}, left=${(n-500)/2/i+t}`,j=window.open(N,"",T);window.focus&&j.focus(),M.current=j}(Q)},isConnected:t,isConnecting:N,sheetStatus:z,handleSheetStatus:function(M){y(M)},handleDisconnectSheet:function(){y(!1,!0)},disconnectSheetModal:I,setDisconnectSheetModal:o}),j&&(0,E.jsx)("div",{className:"formgent-integration-error",children:j})]})}var w=e(71120),C=e(64610);const W=r.Ay.div`
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 1px solid var(--formgent-color-gray-200);
    background: #fff;
    padding: 18px 20px;
    position: fixed;
    width: 100%;
    z-index: 99998;
    left: 160px;
    top: 32px;
    .formgent-settings-header-breadcrumb{
        display: flex;
        align-items: center;
        gap: 5px;
        svg{
            width: 17px;
            height: 17px;
            path{
                stroke: #7E8C9A;
            }
        }
    }
    span{
        color: #4a545e;
        font-size: 13px;
        font-weight: 500;
    }
    .formgent-settings-header-breadcrumb__icon{
        line-height: 0;
    }
`,B=r.Ay.div`
    display: flex;
    min-height: calc(100vh - 103px);
    overflow: hidden;
    padding-top: 71px;
    font-family: "Inter", sans-serif;
    .ant-input-affix-wrapper{
        padding: 0 16px;
        border-radius: 6px;
        box-shadow: 0 0;
        border-width: 1px;
        &:hover{
            border-color: transparent;
        }
        &.ant-input-outlined{
            &:focus,
            &:focus-within{
                border-color: #2271b1;
                box-shadow: 0 0 0 1px #2271b1;
            }
        }
        .ant-input{
            box-shadow: 0 0;
        }
    }
    .formgent-settings-sider{
        height: auto;
    }
    .formgent-settings-sider__nav{
        padding: 16px 12px;
    }
    .formgent-editor-wrap__content__main{
        background: #fff;
        flex: 1;
    }
    .formgent-settings-content{
        padding: 24px 60px;
        .formgent-settings-content__title{
            color: var(--formgent-color-dark);
            font-size: 19px;
            font-weight: 600;
            margin: 0 0 24px;
            font-family: "Inter", sans-serif;
        }
    }
    .formgent-settings-content__item{
        .formgent-form-group{
            .ant-form-item-label{
                color: var(--formgent-color-dark);
                font-size: 14px;
                font-weight: 500;
            }
            .formgent-save-settings-btn{
                padding-left: 24px;
                padding-right: 24px;
            }
        }
    }

    .formgent-settings-content__payment{
        margin-top: 40px;
        max-width: 830px;
        .formgent-settings-content__payment-title{
            font-size: 16px;
            font-weight: 600;
            margin: 0 0 16px;
            display: inline-block;
            color: var(--formgent-color-gray-900);
        }
        .formgent-settings-content__toggle{
            max-width: 690px;
        }  
    }

    .formgent-settings-content__field{
        .formgent-settings-content__field-label{
            font-size: 15px;
            font-weight: 500;
            display: inline-block;
            margin-bottom: 12px; 
            color: var(--formgent-color-dark);
        }
        .formgent-ant-select,
        .ant-select{
            width: 100%;
        }
        .formgent-settings-content__field-note{
            display: inline-block;
            font-size: 13px;
            margin-top: 8px;
        }
    }
`,b=r.Ay.div`
    .formgent-settings-tab-nav{
        display: flex;
        align-items: center;
        margin-bottom: 30px;
        border-bottom: 1px solid var(--formgent-color-gray-200);
    }
    .formgent-settings-tab-nav__item{
        font-size: 13px;
        font-weight: 500;
        padding: 14px;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 4px;
        margin-bottom: -1px;
        border-bottom: 2px solid transparent;
        color: var(--formgent-color-gray-900);
        &.formgent-active{
            border-color: var(--formgent-color-primary);
        }
    }
    .formgent-settings-toggle{
        border: 0 none;
        padding: 0;
        align-items: flex-start;
        box-shadow: 0 0;
        margin-bottom: 24px;
    }
    .formgent-settings-form-fields__label{
        font-size: 14px;
        font-weight: 500;
        margin: 0 0 4px;
        color: var(--formgent-color-gray-900);
    }
    .formgent-settings-form-fields__description{
        font-size: 12px;
        color: var(--formgent-color-gray-500);
    }
    .formgent-settings-tab-content-fields{
        .formgent-ant-input{
            margin-top: 12px;
        }
        .ant-input-affix-wrapper{
            background: none;
            border-radius: 6px;
            border: 1px solid var(--formgent-color-gray-200);
            box-shadow: 0px 1px 2px 0px rgba(16, 24, 40, 0.05);
            min-height: 46px;
            padding: 0 16px;
        }
    }
`;var f=e(37628);const J=(0,f.MA)(formgent_data?.currencies),G=(0,f.UK)(formgent_data?.currency_symbols);function v({handleUpdatePayment:M,handleUpdateSettings:N}){const{settings:e}=(0,g.useSelect)(M=>{const{getSettings:N}=M(T.A.GLOBAL_SETTINGS);return{settings:N()}},[]),t=[{label:`Left (${G[e?.payment?.currency]}100)`,value:"left"},{label:`Right (100${G[e?.payment?.currency]})`,value:"right"},{label:`Left Space (${G[e?.payment?.currency]} 100)`,value:"left_space"},{label:`Right Space (100 ${G[e?.payment?.currency]})`,value:"right_space"}];return(0,E.jsxs)("div",{className:"formgent-settings-currency",children:[(0,E.jsxs)("div",{className:"formgent-settings-content__field formgent-mb-20",children:[(0,E.jsxs)("span",{className:"formgent-settings-content__field-label",children:[(0,D.__)(" Default currency","formgent")," "]}),(0,E.jsx)(I.AntSelect,{showSearch:!0,value:J.filter(M=>M.value===e?.payment?.currency)[0],onChange:N=>M(N,"currency"),filterOption:(M,N)=>{var e;return(null!==(e=N?.label)&&void 0!==e?e:"").toLowerCase().includes(M.toLowerCase())},placeholder:(0,D.__)("Select default currency","formgent"),options:J,popupClassName:"formgent-select-payment-currency"})]}),(0,E.jsxs)("div",{className:"formgent-settings-content__field",children:[(0,E.jsx)("span",{className:"formgent-settings-content__field-label",children:(0,D.__)("Currency Sign Position","formgent")}),(0,E.jsx)(I.AntRadioGroup,{value:e?.payment?.symbol_position,options:t,onChange:N=>M(N.target.value,"symbol_position")})]}),(0,E.jsx)(I.AntButton,{type:"primary",className:"formgent-save-settings-btn formgent-mt-20",htmlType:"button",onClick:N,children:(0,D.__)("Save Currency","formgent")})]})}function X({handleUpdateSettings:M,handleUpdatePayment:N}){const{settings:e,loading:t}=(0,g.useSelect)(M=>{const{getSettings:N,isLoading:e}=M(T.A.GLOBAL_SETTINGS);return{settings:N(),loading:e()}},[]);return(0,c.useEffect)(()=>{(0,g.resolveSelect)("formgent").fetchPages()},[]),(0,E.jsxs)("div",{className:"formgent-settings-page-configuration",style:{width:"100%"},children:[(0,E.jsxs)("div",{className:"formgent-settings-content__field formgent-mb-20",children:[(0,E.jsxs)("span",{className:"formgent-settings-content__field-label",children:[(0,D.__)("Payment success page","formgent")," "]}),(0,E.jsx)(I.WPPages,{handleInputChange:(M,e)=>{N(e,"success_page")},pageSelected:e?.payment?.success_page}),(0,E.jsxs)("span",{className:"formgent-settings-content__field-note",children:[(0,D.__)("Use the following shortcode to display the payment success message","formgent"),": ",(0,E.jsx)("code",{children:"[formgent_payment_success]"})]})]}),(0,E.jsxs)("div",{className:"formgent-settings-content__field formgent-mb-20",children:[(0,E.jsxs)("span",{className:"formgent-settings-content__field-label",children:[(0,D.__)("Payment Failed page","formgent")," "]}),(0,E.jsx)(I.WPPages,{handleInputChange:(M,e)=>{N(e,"failed_page")},pageSelected:e?.payment?.failed_page}),(0,E.jsxs)("span",{className:"formgent-settings-content__field-note",children:[(0,D.__)("Use the following shortcode to display the payment failed message","formgent"),": ",(0,E.jsx)("code",{children:"[formgent_payment_failed]"})]})]}),(0,E.jsx)(I.AntButton,{type:"primary",className:"formgent-save-settings-btn",htmlType:"button",onClick:M,children:(0,D.__)("Save Page Configuration","formgent")})]})}function H({handleUpdatePayment:M,handleUpdateSettings:N}){const[e]=I.AntForm.useForm(),{settings:t}=(0,g.useSelect)(M=>{const{getSettings:N}=M(T.A.GLOBAL_SETTINGS);return{settings:N()}},[]);return(0,c.useEffect)(()=>{t?.payment?.paypal&&e.setFieldsValue(t?.payment?.paypal)},[t?.payment?.paypal]),(0,E.jsxs)(E.Fragment,{children:[(0,E.jsx)(I.Toggle,{title:(0,D.__)("Enable PayPal","formgent"),description:(0,D.__)("Easily collect PayPal Checkout and credit card payments with PayPal .","formgent"),checked:t?.payment?.paypal?.status||!1,onChange:N=>{M(N,"status","paypal")}}),t?.payment?.paypal.status&&(0,E.jsx)(I.Toggle,{title:(0,D.__)("Enable Test Mode","formgent"),description:(0,D.__)("Prevent PayPal Commerce from processing live transactions. Please, see <a href='https://developer.paypal.com/tools/sandbox/'>PayPal Commerce documentation</a> for full details.","formgent"),checked:t?.payment?.paypal?.test_mode||!1,onChange:N=>{M(N,"test_mode","paypal")}}),(0,E.jsxs)("div",{className:"formgent-settings-tab-content-wrap",children:[t?.payment?.paypal.status&&(0,E.jsxs)(E.Fragment,{children:[(0,E.jsx)("h4",{className:"formgent-settings-form-fields__label",children:(0,D.__)("Connect your PayPal Account to your website to accept Payments","formgent")}),(0,E.jsx)("span",{className:"formgent-settings-form-fields__description",children:(0,D.__)("Securely connect to PayPal with just a few clicks to begin accepting payments!","formgent")})]}),(0,E.jsx)("div",{className:"formgent-settings-tab-content-fields",children:(0,E.jsxs)(I.AntForm,{form:e,onFinish:N,children:[t?.payment?.paypal.status&&(0,E.jsxs)(E.Fragment,{children:[(0,E.jsx)(I.AntFormItem,{name:"client_id",rules:[{required:!0,message:(0,D.__)("Client Id is required!","formgent")}],children:(0,E.jsx)(I.AntInput,{className:"formgent-form-field",value:t?.payment?.paypal?.client_id||"",placeholder:(0,D.__)("Client id","formgent"),onChange:N=>M(N.target.value,"client_id","paypal")})}),(0,E.jsx)(I.AntFormItem,{name:"secret_key",rules:[{required:!0,message:(0,D.__)("Secret Key is required!","formgent")}],children:(0,E.jsx)(I.AntInputPassword,{className:"formgent-form-field",placeholder:(0,D.__)("Secret key","formgent"),value:t?.payment?.paypal?.secret_key||"",onChange:N=>M(N.target.value,"secret_key","paypal")})})]}),(0,E.jsx)(I.AntButton,{type:"primary",className:"formgent-save-settings-btn",htmlType:"submit",children:(0,D.__)("Save changes","formgent")})]})})]})]})}var _=e(13526);function P({handleUpdatePayment:M,handleUpdateSettings:N}){const[e]=I.AntForm.useForm(),{settings:t}=(0,g.useSelect)(M=>{const{getSettings:N}=M(T.A.GLOBAL_SETTINGS);return{settings:N()}},[]);return(0,c.useEffect)(()=>{t?.payment?.stripe&&e.setFieldsValue(t?.payment?.stripe)},[t?.payment?.stripe]),(0,E.jsxs)(E.Fragment,{children:[(0,E.jsx)(I.Toggle,{title:(0,D.__)("Enable Stripe","formgent"),description:(0,D.__)("Easily collect Stripe Checkout and credit card payments with Stripe.","formgent"),checked:t?.payment?.stripe?.status||!1,onChange:N=>{M(N,"status","stripe")}}),t?.payment?.stripe?.status&&(0,E.jsx)(I.Toggle,{title:(0,D.__)("Enable Test Mode","formgent"),description:(0,D.__)("Prevent Stripe from processing live transactions.","formgent"),checked:t?.payment?.stripe?.test_mode||!1,onChange:N=>{M(N,"test_mode","stripe")}}),(0,E.jsxs)("div",{className:"formgent-settings-tab-content-wrap",children:[t?.payment?.stripe?.status&&(0,E.jsxs)(E.Fragment,{children:[(0,E.jsx)("h4",{className:"formgent-settings-form-fields__label",children:(0,D.__)("Connect your Stripe Account to your website to accept Payments","formgent")}),(0,E.jsx)("span",{className:"formgent-settings-form-fields__description",children:(0,D.__)("Securely connect to Stripe with just a few clicks to begin accepting payments!","formgent")})]}),(0,E.jsx)("div",{className:"formgent-settings-tab-content-fields",children:(0,E.jsxs)(I.AntForm,{form:e,onFinish:N,children:[t?.payment?.stripe?.status&&(0,E.jsxs)(E.Fragment,{children:[(0,E.jsx)(I.AntFormItem,{name:"publishable_key",rules:[{required:!0,message:(0,D.__)("Publishable Key is required!","formgent")}],children:(0,E.jsx)(I.AntInput,{className:"formgent-form-field",value:t?.payment?.stripe?.publishable_key||"",placeholder:(0,D.__)("Publishable key","formgent"),onChange:N=>M(N.target.value,"publishable_key","stripe")})}),(0,E.jsx)(I.AntFormItem,{name:"secret_key",rules:[{required:!0,message:(0,D.__)("Secret Key is required!","formgent")}],children:(0,E.jsx)(I.AntInputPassword,{className:"formgent-form-field",placeholder:(0,D.__)("Secret key","formgent"),value:t?.payment?.stripe?.secret_key||"",onChange:N=>M(N.target.value,"secret_key","stripe")})})]}),(0,E.jsx)(I.AntButton,{type:"primary",className:"formgent-save-settings-btn",htmlType:"submit",children:(0,D.__)("Save changes","formgent")})]})})]})]})}function K(){const[M,N]=(0,c.useState)("paypal"),{settings:e,loading:t}=(0,g.useSelect)(M=>{const{getSettings:N,isLoading:e}=M(T.A.GLOBAL_SETTINGS);return{settings:N(),loading:e()}},[]),{updatePaymentSettings:i,updateSettings:j,invalidateResolution:a}=(0,g.useDispatch)(T.A.GLOBAL_SETTINGS),{invalidateResolution:l}=(0,g.useDispatch)("formgent");function o(M,N,t){let g;g=t?{...e?.payment,[t]:{...e?.payment?.[t],[N]:M}}:{...e?.payment,[N]:M},i(g)}async function U(){try{await j({payment:{...e?.payment}}),(0,n.doAction)("formgent-toast",{message:(0,D.__)("Payment Settings Updated Successfully","formgent"),type:"success"})}catch(M){console.error(M)}}return(0,E.jsxs)("div",{className:"formgent-settings-content",children:[(0,E.jsxs)("div",{className:"formgent-settings-content__head",children:[(0,E.jsx)("h2",{className:"formgent-settings-content__title",children:(0,D.__)("Payment Settings","formgent")}),(0,E.jsx)("p",{className:"formgent-settings-content__description",children:(0,D.__)("By toggling the switch, users can enable or disable payments.","formgent")})]}),(0,E.jsx)("div",{className:"formgent-settings-content__payment",children:(0,E.jsx)("div",{className:"formgent-settings-content__toggle",children:(0,E.jsx)(I.Toggle,{title:(0,D.__)("Enable Payment","formgent"),description:(0,D.__)("To process payments with FormGent, this needs to be enabled.","formgent"),checked:"1"===e?.payment?.status,onChange:async function(M){o(M?"1":"0","status");try{await j({payment:{...e?.payment,status:M?"1":"0"}}),a("getSettings"),l("fetchPages"),(0,n.doAction)("formgent-toast",{message:M?(0,D.__)("Payment Status Disabled","formgent"):(0,D.__)("Payment Status Enabled","formgent"),type:"success"})}catch(M){console.error(M)}}})})}),"1"===e?.payment?.status&&(0,E.jsxs)(E.Fragment,{children:[(0,E.jsxs)("div",{className:"formgent-settings-content__payment",children:[(0,E.jsx)("span",{className:"formgent-settings-content__payment-title",children:(0,D.__)("Page configuration","formgent")}),(0,E.jsx)("div",{className:"formgent-card-action",children:(0,E.jsx)(X,{handleUpdatePayment:o,handleUpdateSettings:U})})]}),(0,E.jsxs)("div",{className:"formgent-settings-content__payment",children:[(0,E.jsx)("span",{className:"formgent-settings-content__payment-title",children:(0,D.__)("Currency","formgent")}),(0,E.jsx)("div",{className:"formgent-card-action",children:(0,E.jsx)(v,{handleUpdatePayment:o,handleUpdateSettings:U})})]}),(0,E.jsxs)("div",{className:"formgent-settings-content__payment",children:[(0,E.jsx)("span",{className:"formgent-settings-content__payment-title",children:(0,D.__)("Available Payment Gateways (2)","formgent")}),(0,E.jsx)("div",{className:"formgent-card-action",children:(0,E.jsxs)(b,{children:[(0,E.jsxs)("div",{className:"formgent-settings-tab-nav",children:[(0,E.jsxs)("span",{className:(0,_.A)("formgent-settings-tab-nav__item",{"formgent-active":"paypal"===M}),onClick:()=>N("paypal"),children:[(0,E.jsx)(s.A,{src:w.A}),(0,D.__)("Paypal","formgent")]}),(0,E.jsxs)("span",{className:(0,_.A)("formgent-settings-tab-nav__item",{"formgent-active":"Stripe"===M}),onClick:()=>N("Stripe"),children:[(0,E.jsx)(s.A,{src:C.A}),(0,D.__)("Stripe","formgent")]})]}),(0,E.jsxs)("div",{className:"formgent-settings-tab-content",children:["paypal"===M&&(0,E.jsx)(H,{handleUpdatePayment:o,handleUpdateSettings:U}),"Stripe"===M&&(0,E.jsx)(P,{handleUpdatePayment:o,handleUpdateSettings:U})]})]})})]})]})]})}const q=k("LicenseSettings");function $(){const{disable_ip_logging:M,loading:N}=(0,g.useSelect)(M=>{const{getIPLogging:N,isLoading:e}=M(T.A.GLOBAL_SETTINGS);return{disable_ip_logging:N(),loading:e()}},[]),{updateDisableIPLogging:e,updateSettings:t}=(0,g.useDispatch)(T.A.GLOBAL_SETTINGS);return(0,E.jsxs)("div",{className:"formgent-settings-content",children:[(0,E.jsx)("div",{className:"formgent-settings-content__head",children:(0,E.jsx)("h4",{className:"formgent-settings-content__title",children:(0,D.__)("General Settings","formgent")})}),N?(0,E.jsx)(I.AntSkeleton,{active:!0}):(0,E.jsx)("div",{className:"formgent-settings-content__item",children:(0,E.jsx)("div",{className:"formgent-settings-content__toggle",children:(0,E.jsx)(I.Toggle,{title:(0,D.__)("Disable IP Logging","formgent"),description:(0,D.__)("When enabled, the user's IP address will not be saved with the form submission.","formgent"),checked:"yes"===M||!0===M,onChange:async function(M){e(M?"yes":"no");try{await t({disable_ip_logging:M?"yes":"no"}),(0,n.doAction)("formgent-toast",{message:M?(0,D.__)("IP Logging Disabled","formgent"):(0,D.__)("IP Logging Enabled","formgent"),type:"success"})}catch(M){console.error(M)}}})})})]})}var MM=e(606);const NM="formgent-settings-breadcrumb";function eM(){return(0,E.jsxs)(W,{className:"formgent-settings-header",children:[(0,E.jsx)("div",{className:"formgent-settings-header-log",children:(0,E.jsx)(s.A,{src:MM.A})}),(0,E.jsxs)("div",{className:"formgent-settings-header-breadcrumb",children:[(0,E.jsx)("span",{className:"formgent-settings-header-breadcrumb__icon",children:(0,E.jsx)(s.A,{src:"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxMDAlIiBoZWlnaHQ9IjEwMCUiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSI+Cgk8cGF0aCBkPSJNOSA2TDE1IDEyTDkgMTgiIHN0cm9rZT0iY3VycmVudENvbG9yIiBzdHJva2Utd2lkdGg9IjIiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8L3N2Zz4K"})}),(0,E.jsx)("span",{children:(0,D.__)("Settings","formgent")}),(0,E.jsx)(t.Slot,{name:NM})]})]})}const tM=r.Ay.div`
	border-radius: 10px;
	border: 1px solid var( --formgent-color-gray-200 );
	background: #fff;
	box-shadow: 0px 1px 2px 0px rgba( 16, 24, 40, 0.05 );
	padding: 16px 24px;
	display: flex;
	flex-wrap: wrap;
	gap: 20px;
	align-items: center;
	align-self: stretch;
	width: 100%;
	max-width: 780px;
	.formgent-settings-toggle-info {
		display: flex;
		align-items: center;
		justify-content: space-between;
		gap: 20px;
		flex: 1;
	}
	.formgent-settings-toggle-info-content,
	.formgent-settings-info {
		h3 {
			margin: 0 0 6px;
			color: var( --formgent-color-dark );
			font-size: 15px;
			font-weight: 500;
		}
		span,
		p {
			color: var( --formgent-color-gray-600 );
			font-size: 13px;
			font-weight: 400;
			margin: 0;
		}
	}
`,gM=r.Ay.div`
	margin-top: 4px;
	.formgent-ant-segmented {
		margin-bottom: 20px;
	}
	.ant-segmented {
		padding: 5px;
	}
	.ant-segmented-item {
		padding: 4px 8px;
		.ant-segmented-item-label {
			display: flex;
			align-items: center;
			gap: 8px;
			color: var( --formgent-color-gray-600 );
			svg {
				width: 24px;
				height: 24px;
			}
		}
	}
	.ant-segmented-item-selected {
		.ant-segmented-item-label {
			color: var( --formgent-color-dark );
		}
	}
	.ant-form-item {
		.ant-form-item-label {
			color: var( --formgent-color-dark );
			font-size: 14px;
			font-weight: 500;
		}
	}
	.formgent-settings-action {
		.ant-btn {
			padding-left: 24px;
			padding-right: 24px;
		}
	}
`,nM=[{value:"recaptcha",icon:"data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGcgaWQ9InJlY2FwdGNoYSI+CjxnIGlkPSJHcm91cCAzMzYzIj4KPHBhdGggaWQ9IlZlY3RvciIgZD0iTTIyLjAwMDkgMTEuOTg2M0MyMi4wMDA1IDExLjg0MjYgMjEuOTk3MiAxMS42OTk2IDIxLjk5MDUgMTEuNTU3M1YzLjQ0MDEyTDE5Ljc1MzIgNS42ODQxN0MxNy45MjIyIDMuNDM1NzggMTUuMTM2MiAyIDEyLjAxNjIgMkM4Ljc2ODg4IDIgNS44ODQyMSAzLjU1NDQgNC4wNjA1NSA1Ljk2MTg0TDcuNzI3ODggOS42NzkwOUM4LjA4Njk5IDkuMDEzNzcgOC41OTU2MiA4LjQ0MTMzIDkuMjEzNTQgOC4wMDcwOEM5Ljg1Mzg4IDcuNTA2MjEgMTAuNzYxMiA3LjA5NjIzIDEyLjAxNTkgNy4wOTYyM0MxMi4xNjc1IDcuMDk2MjMgMTIuMjg0NSA3LjExNDI3IDEyLjM3MDUgNy4xNDc2OUMxMy45MjU1IDcuMjcwNjUgMTUuMjczMiA4LjEzMTM4IDE2LjA2NjkgOS4zODAzOEwxMy40NzA5IDExLjk4NDNDMTYuNzU4OSAxMS45NzEzIDIwLjQ3MzUgMTEuOTYzNiAyMi4wMDA1IDExLjk4NiIgZmlsbD0iIzFDM0FBOSIvPgo8cGF0aCBpZD0iVmVjdG9yXzIiIGQ9Ik0xMS45NTcgMi4wMDA0OUMxMS44MTMzIDIuMDAxMTYgMTEuNjcxIDIuMDA0NSAxMS41MjkgMi4wMTA4NUgzLjQzNTY3TDUuNjczMzMgNC4yNTQ5QzMuNDMxNjcgNi4wOTE2NCAyIDguODg1MzQgMiAxMi4wMTQ5QzIgMTUuMjcxNyAzLjU1IDE4LjE2NTMgNS45NTAzMyAxOS45OTRMOS42NTYzMyAxNi4zMTU4QzguOTkyODkgMTUuOTU1NyA4LjQyMjI2IDE1LjQ0NTYgNy45ODk2NiAxNC44MjU5QzcuNDkgMTQuMTgzNyA3LjA4MTMzIDEzLjI3MzkgNy4wODEzMyAxMi4wMTUyQzcuMDgxMzMgMTEuODYzMiA3LjA5OSAxMS43NDU1IDcuMTMyMzMgMTEuNjU5M0M3LjI1NTMzIDEwLjA5OTkgOC4xMTMzMyA4Ljc0ODM1IDkuMzU4NjYgNy45NTI0NEwxMS45NTQ3IDEwLjU1NkMxMS45NDE3IDcuMjU4MSAxMS45MzQgMy41MzI1IDExLjk1NjMgMi4wMDA4MiIgZmlsbD0iIzQyODVGNCIvPgo8cGF0aCBpZD0iVmVjdG9yXzMiIGQ9Ik0yIDEyLjAxNDZDMi4wMDA2NyAxMi4xNTgzIDIuMDA0IDEyLjMwMTMgMi4wMTAzMyAxMi40NDM3VjIwLjU2MDhMNC4yNDc2NyAxOC4zMTY4QzYuMDc5IDIwLjU2NTIgOC44NjQ2NiAyMi4wMDA5IDExLjk4NSAyMi4wMDA5QzE1LjIzMiAyMi4wMDA5IDE4LjExNjcgMjAuNDQ2NSAxOS45NDAzIDE4LjAzOTFMMTYuMjczIDE0LjMyMTlDMTUuOTEzNyAxNC45ODg4IDE1LjQwMyAxNS41NjEyIDE0Ljc4NzMgMTUuOTkzOUMxNC4xNDcgMTYuNDk0NyAxMy4yNCAxNi45MDQ3IDExLjk4NSAxNi45MDQ3QzExLjgzMzMgMTYuOTA0NyAxMS43MTYzIDE2Ljg4NjcgMTEuNjMwMyAxNi44NTMzQzEwLjA3NTcgMTYuNzMwMyA4LjcyNzY2IDE1Ljg2OTYgNy45MzQzMyAxNC42MjA2TDEwLjUzMDMgMTIuMDE2N0M3LjI0MiAxMi4wMjk3IDMuNTI3NjcgMTIuMDM3NCAyLjAwMDMzIDEyLjAxNSIgZmlsbD0iI0FCQUJBQiIvPgo8L2c+CjwvZz4KPC9zdmc+Cg==",label:"Google"},{value:"hcaptcha",icon:"data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGcgaWQ9ImhjYXB0Y2hhIj4KPGcgaWQ9Ikdyb3VwIDMzNjIiPgo8cGF0aCBpZD0iVmVjdG9yIiBvcGFjaXR5PSIwLjUwMiIgZD0iTTE0LjUgMTkuNTAwNUgxNy4wMDAxVjIyLjAwMDJIMTQuNVYxOS41MDA1WiIgZmlsbD0iIzAwNzRCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzIiIG9wYWNpdHk9IjAuNzAyIiBkPSJNMTEuOTk5NyAxOS41MDA1SDE0LjQ5OTVWMjIuMDAwMkgxMS45OTk3VjE5LjUwMDVaTTkuNSAxOS41MDA1SDExLjk5OTdWMjIuMDAwMkg5LjVWMTkuNTAwNVoiIGZpbGw9IiMwMDc0QkYiLz4KPHBhdGggaWQ9IlZlY3Rvcl8zIiBvcGFjaXR5PSIwLjUwMiIgZD0iTTcgMTkuNTAwNUg5LjUwMDA4VjIyLjAwMDJIN1YxOS41MDA1WiIgZmlsbD0iIzAwNzRCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzQiIG9wYWNpdHk9IjAuNzAyIiBkPSJNMTcgMTdIMTkuNTAwMVYxOS41MDAxSDE3VjE3WiIgZmlsbD0iIzAwODJCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzUiIG9wYWNpdHk9IjAuOCIgZD0iTTE0LjUgMTdIMTcuMDAwMVYxOS41MDAxSDE0LjVWMTdaIiBmaWxsPSIjMDA4MkJGIi8+CjxwYXRoIGlkPSJWZWN0b3JfNiIgZD0iTTExLjk5OTcgMTdIMTQuNDk5NVYxOS41MDAxSDExLjk5OTdWMTdaTTkuNSAxN0gxMS45OTk3VjE5LjUwMDFIOS41VjE3WiIgZmlsbD0iIzAwODJCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzciIG9wYWNpdHk9IjAuOCIgZD0iTTcgMTdIOS41MDAwOFYxOS41MDAxSDdWMTdaIiBmaWxsPSIjMDA4MkJGIi8+CjxwYXRoIGlkPSJWZWN0b3JfOCIgb3BhY2l0eT0iMC43MDIiIGQ9Ik00LjUgMTdINy4wMDAwOFYxOS41MDAxSDQuNVYxN1oiIGZpbGw9IiMwMDgyQkYiLz4KPGcgaWQ9Ikdyb3VwIj4KPHBhdGggaWQ9IlZlY3Rvcl85IiBvcGFjaXR5PSIwLjUwMiIgZD0iTTE5LjUgMTQuNDk5NUgyMS45OTk4VjE2Ljk5OTZIMTkuNVYxNC40OTk1WiIgZmlsbD0iIzAwOEZCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzEwIiBvcGFjaXR5PSIwLjgiIGQ9Ik0xNyAxNC40OTk1SDE5LjUwMDFWMTYuOTk5NkgxN1YxNC40OTk1WiIgZmlsbD0iIzAwOEZCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzExIiBkPSJNMTQuNDk5NSAxNC40OTk1SDE2Ljk5OTZWMTYuOTk5NkgxNC40OTk1VjE0LjQ5OTVaTTExLjk5OTcgMTQuNDk5NUgxNC40OTk1VjE2Ljk5OTZIMTEuOTk5N1YxNC40OTk1Wk05LjUgMTQuNDk5NUgxMS45OTk3VjE2Ljk5OTZIOS41VjE0LjQ5OTVaIiBmaWxsPSIjMDA4RkJGIi8+CjxwYXRoIGlkPSJWZWN0b3JfMTIiIGQ9Ik03IDE0LjQ5OTVIOS41MDAwOFYxNi45OTk2SDdWMTQuNDk5NVoiIGZpbGw9IiMwMDhGQkYiLz4KPHBhdGggaWQ9IlZlY3Rvcl8xMyIgb3BhY2l0eT0iMC44IiBkPSJNNC41IDE0LjQ5OTVINy4wMDAwOFYxNi45OTk2SDQuNVYxNC40OTk1WiIgZmlsbD0iIzAwOEZCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzE0IiBvcGFjaXR5PSIwLjUwMiIgZD0iTTIgMTQuNDk5NUg0LjQ5OTc1VjE2Ljk5OTZIMlYxNC40OTk1WiIgZmlsbD0iIzAwOEZCRiIvPgo8L2c+CjxwYXRoIGlkPSJWZWN0b3JfMTUiIG9wYWNpdHk9IjAuNzAyIiBkPSJNMTkuNSAxMkgyMS45OTk4VjE0LjQ5OThIMTkuNVYxMloiIGZpbGw9IiMwMDlEQkYiLz4KPHBhdGggaWQ9IlZlY3Rvcl8xNiIgZD0iTTE2Ljk5OTkgMTJIMTkuNVYxNC40OTk4SDE2Ljk5OTlWMTJaTTE0LjQ5OTUgMTJIMTYuOTk5NlYxNC40OTk4SDE0LjQ5OTVWMTJaTTExLjk5OTcgMTJIMTQuNDk5NVYxNC40OTk4SDExLjk5OTdWMTJaTTkuNSAxMkgxMS45OTk3VjE0LjQ5OThIOS41VjEyWiIgZmlsbD0iIzAwOURCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzE3IiBkPSJNNy4wMDA0MiAxMkg5LjUwMDVWMTQuNDk5OEg3LjAwMDQyVjEyWk00LjUgMTJINy4wMDAwOFYxNC40OTk4SDQuNVYxMloiIGZpbGw9IiMwMDlEQkYiLz4KPHBhdGggaWQ9IlZlY3Rvcl8xOCIgb3BhY2l0eT0iMC43MDIiIGQ9Ik0yIDEySDQuNDk5NzVWMTQuNDk5OEgyVjEyWiIgZmlsbD0iIzAwOURCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzE5IiBvcGFjaXR5PSIwLjcwMiIgZD0iTTE5LjUgOS41MDA0OUgyMS45OTk4VjEyLjAwMDJIMTkuNVY5LjUwMDQ5WiIgZmlsbD0iIzAwQUJCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzIwIiBkPSJNMTYuOTk5OSA5LjUwMDQ5SDE5LjVWMTIuMDAwMkgxNi45OTk5VjkuNTAwNDlaTTE0LjQ5OTUgOS41MDA0OUgxNi45OTk2VjEyLjAwMDJIMTQuNDk5NVY5LjUwMDQ5Wk0xMS45OTk3IDkuNTAwNDlIMTQuNDk5NVYxMi4wMDAySDExLjk5OTdWOS41MDA0OVpNOS41IDkuNTAwNDlIMTEuOTk5N1YxMi4wMDAySDkuNVY5LjUwMDQ5WiIgZmlsbD0iIzAwQUJCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzIxIiBkPSJNNy4wMDA0MiA5LjUwMDQ5SDkuNTAwNVYxMi4wMDAySDcuMDAwNDJWOS41MDA0OVpNNC41IDkuNTAwNDlINy4wMDAwOFYxMi4wMDAySDQuNVY5LjUwMDQ5WiIgZmlsbD0iIzAwQUJCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzIyIiBvcGFjaXR5PSIwLjcwMiIgZD0iTTIgOS41MDA0OUg0LjQ5OTc1VjEyLjAwMDJIMlY5LjUwMDQ5WiIgZmlsbD0iIzAwQUJCRiIvPgo8ZyBpZD0iR3JvdXBfMiI+CjxwYXRoIGlkPSJWZWN0b3JfMjMiIG9wYWNpdHk9IjAuNTAyIiBkPSJNMTkuNSA3SDIxLjk5OThWOS41MDAwOEgxOS41VjdaIiBmaWxsPSIjMDBCOUJGIi8+CjxwYXRoIGlkPSJWZWN0b3JfMjQiIG9wYWNpdHk9IjAuOCIgZD0iTTE3IDdIMTkuNTAwMVY5LjUwMDA4SDE3VjdaIiBmaWxsPSIjMDBCOUJGIi8+CjxwYXRoIGlkPSJWZWN0b3JfMjUiIGQ9Ik0xNC40OTk1IDdIMTYuOTk5NlY5LjUwMDA4SDE0LjQ5OTVWN1pNMTEuOTk5NyA3SDE0LjQ5OTVWOS41MDAwOEgxMS45OTk3VjdaTTkuNSA3SDExLjk5OTdWOS41MDAwOEg5LjVWN1oiIGZpbGw9IiMwMEI5QkYiLz4KPHBhdGggaWQ9IlZlY3Rvcl8yNiIgZD0iTTcgN0g5LjUwMDA4VjkuNTAwMDhIN1Y3WiIgZmlsbD0iIzAwQjlCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzI3IiBvcGFjaXR5PSIwLjgiIGQ9Ik00LjUgN0g3LjAwMDA4VjkuNTAwMDhINC41VjdaIiBmaWxsPSIjMDBCOUJGIi8+CjxwYXRoIGlkPSJWZWN0b3JfMjgiIG9wYWNpdHk9IjAuNTAyIiBkPSJNMiA3SDQuNDk5NzVWOS41MDAwOEgyVjdaIiBmaWxsPSIjMDBCOUJGIi8+CjwvZz4KPHBhdGggaWQ9IlZlY3Rvcl8yOSIgb3BhY2l0eT0iMC43MDIiIGQ9Ik0xNyA0LjQ5OTUxSDE5LjUwMDFWNi45OTk1OUgxN1Y0LjQ5OTUxWiIgZmlsbD0iIzAwQzZCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzMwIiBvcGFjaXR5PSIwLjgiIGQ9Ik0xNC41IDQuNDk5NTFIMTcuMDAwMVY2Ljk5OTU5SDE0LjVWNC40OTk1MVoiIGZpbGw9IiMwMEM2QkYiLz4KPHBhdGggaWQ9IlZlY3Rvcl8zMSIgZD0iTTExLjk5OTcgNC40OTk1MUgxNC40OTk1VjYuOTk5NTlIMTEuOTk5N1Y0LjQ5OTUxWk05LjUgNC40OTk1MUgxMS45OTk3VjYuOTk5NTlIOS41VjQuNDk5NTFaIiBmaWxsPSIjMDBDNkJGIi8+CjxwYXRoIGlkPSJWZWN0b3JfMzIiIG9wYWNpdHk9IjAuOCIgZD0iTTcgNC40OTk1MUg5LjUwMDA4VjYuOTk5NTlIN1Y0LjQ5OTUxWiIgZmlsbD0iIzAwQzZCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzMzIiBvcGFjaXR5PSIwLjcwMiIgZD0iTTQuNSA0LjQ5OTUxSDcuMDAwMDhWNi45OTk1OUg0LjVWNC40OTk1MVoiIGZpbGw9IiMwMEM2QkYiLz4KPHBhdGggaWQ9IlZlY3Rvcl8zNCIgb3BhY2l0eT0iMC41MDIiIGQ9Ik0xNC41IDJIMTcuMDAwMVY0LjQ5OTc1SDE0LjVWMloiIGZpbGw9IiMwMEQ0QkYiLz4KPHBhdGggaWQ9IlZlY3Rvcl8zNSIgb3BhY2l0eT0iMC43MDIiIGQ9Ik0xMS45OTk3IDJIMTQuNDk5NVY0LjQ5OTc1SDExLjk5OTdWMlpNOS41IDJIMTEuOTk5N1Y0LjQ5OTc1SDkuNVYyWiIgZmlsbD0iIzAwRDRCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzM2IiBvcGFjaXR5PSIwLjUwMiIgZD0iTTcgMkg5LjUwMDA4VjQuNDk5NzVIN1YyWiIgZmlsbD0iIzAwRDRCRiIvPgo8cGF0aCBpZD0iVmVjdG9yXzM3IiBkPSJNOC41ODI0NiAxMS4yMTEzTDkuMjc5MDggOS42NTIyMUM5LjUzMzEgOS4yNTI2NyA5LjQ5OTM4IDguNzYzIDkuMjIxNjcgOC40ODUyOUM5LjIxMjMyIDguNDc1OTQgOS4yMDI5OCA4LjQ2NjkzIDkuMTkyOTYgOC40NTgyNUM5LjE4MzI4IDguNDQ5NTcgOS4xNzMyNyA4LjQ0MDg5IDkuMTYzMjYgOC40MzI4OEM5LjE0Mjc3IDguNDE2NDUgOS4xMjEzNyA4LjQwMTE5IDkuMDk5MTcgOC4zODcxNUM5LjA1MTc2IDguMzU3OTkgOS4wMDEyNyA4LjMzNDE1IDguOTQ4NjMgOC4zMTYwNkM4Ljg5NTg5IDguMjk3NyA4Ljg0MTQ4IDguMjg1MzUgOC43ODYwOCA4LjI3ODY3QzguNzMwNjcgOC4yNzIzMyA4LjY3NDU5IDguMjcyIDguNjE5MTggOC4yNzc2N0M4LjU2Mzc3IDguMjgzMzQgOC41MDkwMyA4LjI5NDY5IDguNDU2MjkgOC4zMTIzOEM4LjM5NjU0IDguMzMwNzQgOC4zMzg0NiA4LjM1NDc4IDguMjgzMDUgOC4zODM0OEM4LjIyNzcyIDguNDEyNDYgOC4xNzUgOC40NDYxOSA4LjEyNTUxIDguNDg0MjlDOC4wNzYwNiA4LjUyMjcyIDguMDMwMDQgOC41NjUzOSA3Ljk4Nzk4IDguNjExNzlDNy45NDYxOSA4LjY1ODI1IDcuOTA4NTUgOC43MDgyOSA3Ljg3NTUgOC43NjEzM0M3Ljg0Mjc5IDguODE0NCA2LjkyMTg2IDEwLjk4NjQgNi41NjcwNCAxMS45ODY0QzYuMjEyMjIgMTIuOTg2NCA2LjM1Mzc1IDE0LjgyMDYgNy43MjIyOSAxNi4xOTE4QzkuMTczNiAxNy42NDMxIDExLjI3NDggMTcuOTc0NiAxMi42MTUgMTYuOTY4OUMxMi42MjkgMTYuOTYxOSAxMi42NDMgMTYuOTU0MiAxMi42NTY0IDE2Ljk0NjVDMTIuNjcgMTYuOTM4OCAxMi42ODM0IDE2LjkzMDUgMTIuNjk2NCAxNi45MjE4QzEyLjcwOTggMTYuOTEzNSAxMi43MjI1IDE2LjkwNDUgMTIuNzM1MSAxNi44OTUxQzEyLjc0NzggMTYuODg2MSAxMi43NjAyIDE2Ljg3NjQgMTIuNzcyMiAxNi44NjY0TDE2LjkwMjIgMTMuNDE3N0MxNy4xMDI4IDEzLjI1MTggMTcuMzk5NSAxMi45MTAzIDE3LjEzMzEgMTIuNTIwNUMxNi44NzMxIDEyLjE0MDMgMTYuMzgwNSAxMi4zOTkgMTYuMTc5NSAxMi41Mjc1TDEzLjgwMjYgMTQuMjU1OEMxMy43OTE2IDE0LjI2NDkgMTMuNzc4NiAxNC4yNzE5IDEzLjc2NDkgMTQuMjc1OUMxMy43NTA5IDE0LjI3OTkgMTMuNzM2NSAxNC4yODEyIDEzLjcyMjIgMTQuMjc5NUMxMy43MDc4IDE0LjI3NzkgMTMuNjkzOCAxNC4yNzMyIDEzLjY4MTQgMTQuMjY2MkMxMy42Njg3IDE0LjI1OTIgMTMuNjU3NyAxNC4yNDk1IDEzLjY0OTEgMTQuMjM3OEMxMy41ODg2IDE0LjE2MzcgMTMuNTc4IDEzLjk2NzEgMTMuNjcyOCAxMy44ODkzTDE3LjMxNjQgMTAuNzk3MUMxNy42MzEyIDEwLjUxMzcgMTcuNjc0OSAxMC4xMDE1IDE3LjQxOTkgOS44MTkxMUMxNy4xNzEyIDkuNTQyNCAxNi43NzYgOS41NTA3NCAxNi40NTg2IDkuODM2OEwxMy4xNzgxIDEyLjQwMTNDMTMuMTYyNyAxMi40MTQgMTMuMTQ1IDEyLjQyMzMgMTMuMTI2IDEyLjQyODdDMTMuMTA3IDEyLjQzNCAxMy4wODcgMTIuNDM1NyAxMy4wNjczIDEyLjQzM0MxMy4wNDc2IDEyLjQzMDMgMTMuMDI4OSAxMi40MjQgMTMuMDExOCAxMi40MTM3QzEyLjk5NDggMTIuNDAzNiAxMi45Nzk4IDEyLjM5MDMgMTIuOTY4NSAxMi4zNzQzQzEyLjkwMzcgMTIuMzAxNSAxMi44Nzg3IDEyLjE3NzcgMTIuOTUxOCAxMi4xMDQ5TDE2LjY2NjggOC40OTk2NEMxNi43MzY2IDguNDM0NTUgMTYuNzkzIDguMzU2NDQgMTYuODMyNCA4LjI2OTk5QzE2Ljg3MTggOC4xODMyMSAxNi44OTQyIDguMDg5NDEgMTYuODk3NSA3Ljk5NDI4QzE2LjkwMDggNy44OTkxNSAxNi44ODU1IDcuODA0MDIgMTYuODUyMSA3LjcxNDlDMTYuODE4NyA3LjYyNTQ1IDE2Ljc2ODMgNy41NDM2NyAxNi43MDM2IDcuNDczOUMxNi42NzEyIDcuNDM5NTIgMTYuNjM1MSA3LjQwODgyIDE2LjU5NjcgNy4zODIxMUMxNi41NTgxIDcuMzU1MjUgMTYuNTE2OCA3LjMzMjMzIDE2LjQ3MzYgNy4zMTM2OUMxNi40MzAyIDcuMjk1MzMgMTYuMzg1MSA3LjI4MTMxIDE2LjMzOTEgNy4yNzE2M0MxNi4yOTMgNy4yNjIyOCAxNi4yNDU5IDcuMjU3NjEgMTYuMTk4OSA3LjI1Nzk0QzE2LjE1MDggNy4yNTcyOCAxNi4xMDI3IDcuMjYxMjggMTYuMDU1NyA3LjI3MDI5QzE1Ljk2MSA3LjI4NzYzIDE1Ljg3MDcgNy4zMjM0NyAxNS43OSA3LjM3NTc3QzE1Ljc0OTYgNy40MDE4MSAxNS43MTE5IDcuNDMxODUgMTUuNjc3MiA3LjQ2NTIzTDExLjg4MSAxMS4wMzExQzExLjc5MDIgMTEuMTIxOSAxMS42MTI2IDExLjAzMTEgMTEuNTkwOSAxMC45MjVDMTEuNTg4OSAxMC45MTU2IDExLjU4ODIgMTAuOTA1OSAxMS41ODg2IDEwLjg5NjJDMTEuNTg4OSAxMC44ODY2IDExLjU5MDYgMTAuODc2OSAxMS41OTM2IDEwLjg2NzlDMTEuNTk2MiAxMC44NTg1IDExLjYwMDMgMTAuODQ5NSAxMS42MDUzIDEwLjg0MTVDMTEuNjEwMyAxMC44MzMyIDExLjYxNjMgMTAuODI1NSAxMS42MjMzIDEwLjgxODhMMTQuNTI4OSA3LjUxMDI5QzE0LjU5OTMgNy40NDQ4NyAxNC42NTU3IDcuMzY1OTIgMTQuNjk0OCA3LjI3ODE2QzE0LjczNCA3LjE5MDQxIDE0Ljc1NSA3LjA5NTY2IDE0Ljc1NjYgNi45OTk1OUMxNC43NjAyIDYuODA1NjYgMTQuNjg0OCA2LjYxODQgMTQuNTQ3OSA2LjQ4MTIxQzE0LjQxMDggNi4zNDM2OSAxNC4yMjM4IDYuMjY3NTkgMTQuMDI5OSA2LjI3MDU5QzEzLjgzNiA2LjI3MzYgMTMuNjUxNCA2LjM1NTA0IDEzLjUxODUgNi40OTY1N0w5LjExMjE5IDExLjM2ODJDOC45NTQzMSAxMS41MjYxIDguNzIxNjUgMTEuNTM0MSA4LjYxMDg0IDExLjQ0MjNDOC41OTM4MSAxMS40MjkgOC41Nzk3OSAxMS40MTIzIDguNTY5NDUgMTEuMzkzNkM4LjU1ODc2IDExLjM3NDkgOC41NTIwOSAxMS4zNTQyIDguNTQ5NDIgMTEuMzMyOEM4LjU0Njc1IDExLjMxMTEgOC41NDg0MiAxMS4yODk1IDguNTU0MDkgMTEuMjY4OEM4LjU1OTc3IDExLjI0ODEgOC41Njk0NSAxMS4yMjg0IDguNTgyNDYgMTEuMjExM1oiIGZpbGw9IndoaXRlIi8+CjwvZz4KPC9nPgo8L3N2Zz4K",label:"hCaptcha"},{value:"turnstile",icon:"data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPGcgaWQ9IlR1cm5zdGlsZSI+CjxnIGlkPSJHcm91cCAzMzYxIj4KPHBhdGggaWQ9IlZlY3RvciIgZD0iTTIyLjAwMzkgMTMuMDIxNUwxOC45ODU1IDExLjI5MDJMMTguNDYzNyAxMS4wNjU0TDYuMTE3MTkgMTEuMTUxMVYxNy40MThIMjIuMDAxMkwyMi4wMDM5IDEzLjAyMTVaIiBmaWxsPSJ3aGl0ZSIvPgo8cGF0aCBpZD0iVmVjdG9yXzIiIGQ9Ik0xNi41MDc1IDE2LjgzOTlDMTYuNjU0NyAxNi4zMzQxIDE2LjU5ODUgMTUuODY4NSAxNi4zNTIzIDE1LjUyMzNDMTYuMTI3NSAxNS4yMDc2IDE1Ljc0NzYgMTUuMDIyOSAxNS4yOSAxNS4wMDE1TDYuNjMwODQgMTQuODg5MUM2LjU3NDY1IDE0Ljg4OTEgNi41MjY0OCAxNC44NTk3IDYuNDk3MDUgMTQuODE5NkM2LjQ2NzYxIDE0Ljc3NjggNi40NjIyNiAxNC43MjA2IDYuNDc1NjQgMTQuNjY0NEM2LjUwNTA3IDE0LjU3ODcgNi41ODgwMiAxNC41MTcyIDYuNjc5IDE0LjUwOTJMMTUuNDE1OCAxNC4zOTY4QzE2LjQ0ODcgMTQuMzQ4NiAxNy41NzUyIDEzLjUxMTEgMTcuOTY4NiAxMi40ODM1TDE4LjQ2ODkgMTEuMTgzQzE4LjQ5MDMgMTEuMTI2OSAxOC40OTg0IDExLjA3MDcgMTguNDgyMyAxMS4wMTQ1QzE3LjkyMzEgOC40Njk3IDE1LjY1MTIgNi41Njk4MiAxMi45MzUyIDYuNTY5ODJDMTAuNDMwNiA2LjU2OTgyIDguMzA1OTQgOC4xODg3MyA3LjU0NTk5IDEwLjQzMTFDNy4wNTM2MyAxMC4wNjQ1IDYuNDI3NDcgOS44NjkxOSA1Ljc1MzE1IDkuOTMwNzNDNC41NDkgMTAuMDUxMSAzLjU4NTY4IDExLjAxNDUgMy40Njc5NCAxMi4yMTU5QzMuNDM4NTEgMTIuNTI2MyAzLjQ1OTkyIDEyLjgyODcgMy41MzIxNyAxMy4xMDk3QzEuNTcwNzQgMTMuMTY1OSAwIDE0Ljc2ODcgMCAxNi43NDYyQzAgMTYuOTIyOCAwLjAxMzM3OTEgMTcuMDk2NyAwLjAzNDc4NjEgMTcuMjczNEMwLjA0ODE2NTYgMTcuMzU5IDAuMTIwNDE0IDE3LjQyMDUgMC4yMDMzNjYgMTcuNDIwNUgxNi4xODY0QzE2LjI3NzQgMTcuNDIwNSAxNi4zNjMgMTcuMzU2MyAxNi4zODk4IDE3LjI2NTNMMTYuNTA3NSAxNi44Mzk5WiIgZmlsbD0iI0YzODAyMCIvPgo8cGF0aCBpZD0iVmVjdG9yXzMiIGQ9Ik0xOS4yNjY1IDExLjI3NjlDMTkuMTg4OSAxMS4yNzY5IDE5LjEwNTkgMTEuMjc2OSAxOS4wMjgzIDExLjI4NDlDMTguOTcyMSAxMS4yODQ5IDE4LjkyNCAxMS4zMjc3IDE4LjkwMjYgMTEuMzgzOUwxOC41NjU0IDEyLjU1ODZDMTguNDE4MiAxMy4wNjQzIDE4LjQ3NDQgMTMuNTI5OSAxOC43MjA2IDEzLjg3NTFDMTguOTQ1NCAxNC4xOTA5IDE5LjMyNTQgMTQuMzc1NSAxOS43ODI5IDE0LjM5NjlMMjEuNjI2NiAxNC41MDkzQzIxLjY4MjggMTQuNTA5MyAyMS43MzEgMTQuNTM4OCAyMS43NjA0IDE0LjU3ODlDMjEuNzg5OSAxNC42MjE3IDIxLjc5NTIgMTQuNjgzMyAyMS43ODE4IDE0LjczNDFDMjEuNzUyNCAxNC44MTk3IDIxLjY2OTQgMTQuODgxMyAyMS41Nzg1IDE0Ljg4OTNMMTkuNjU3MiAxNS4wMDE3QzE4LjYxNjMgMTUuMDQ5OSAxNy40OTc3IDE1Ljg4NzQgMTcuMTA0NCAxNi45MTQ5TDE2Ljk2MjYgMTcuMjczNUMxNi45MzMxIDE3LjM0MzEgMTYuOTg0IDE3LjQxNTMgMTcuMDYxNiAxNy40MTUzSDIzLjY1NDlDMjMuNzMyNSAxNy40MTUzIDIzLjgwMjEgMTcuMzY3MiAyMy44MjM1IDE3LjI4OTZDMjMuOTM1OSAxNi44ODI4IDI0LjAwMDEgMTYuNDUyIDI0LjAwMDEgMTYuMDEwNUMyNC4wMDAxIDEzLjQwMTUgMjEuODc1NSAxMS4yNzY5IDE5LjI2NjUgMTEuMjc2OVoiIGZpbGw9IiNGQUFFNDAiLz4KPC9nPgo8L2c+Cjwvc3ZnPgo=",label:"Turnstile"}],DM={recaptcha:{title:(0,D.__)("Google reCAPTCHA Settings","formgent"),description:(0,D.__)("Formgent integrates with Google reCAPTCHA, a free service that protects your website from fraud and abuse. %s","formgent"),link:{url:"http://www.google.com/recaptcha/",text:(0,D.__)("Learn more about reCAPTCHA","formgent")}},hcaptcha:{title:(0,D.__)("hCaptcha Settings","formgent"),description:(0,D.__)("Formgent integrates with hCaptcha, a free service that protects your website from fraud and abuse. %s","formgent"),link:{url:"https://www.hcaptcha.com/",text:(0,D.__)("Learn more about hCaptcha","formgent")}},turnstile:{title:(0,D.__)("Cloudflare Turnstile Settings","formgent"),description:(0,D.__)("Formgent integrates with Cloudflare Turnstile, a free service that protects your website from fraud and abuse. %s","formgent"),link:{url:"https://www.cloudflare.com/en-gb/products/turnstile/",text:(0,D.__)("Learn more about Cloudflare Turnstile","formgent")}}};function iM(){const M=(0,g.useSelect)(M=>M(T.A.GLOBAL_SETTINGS).getCaptchaKeys(),[]),{updateCaptchaKeys:N,updateSettings:e}=(0,g.useDispatch)(T.A.GLOBAL_SETTINGS),[t,i]=(0,c.useState)({}),[j]=I.AntForm.useForm(),[a,l]=(0,c.useState)("recaptcha");(0,c.useEffect)(()=>{M&&(i(M),j.setFieldsValue(M))},[M,j]);const o=(M,N)=>{i(e=>({...e,[M]:N}))},{title:U,description:d,link:z}=DM[a]||{};return(0,E.jsxs)(tM,{children:[(0,E.jsx)("div",{className:"formgent-settings-info",children:U&&(0,E.jsxs)(E.Fragment,{children:[(0,E.jsx)("h3",{children:U}),(0,E.jsx)("p",{dangerouslySetInnerHTML:{__html:(0,D.sprintf)(d,`<a href="${z.url}" target="_blank">${z.text}</a>`)}})]})}),(0,E.jsxs)(gM,{className:"formgent-settings-captcha",children:[(0,E.jsx)(I.AntSegmented,{options:nM.map(({value:M,icon:N,label:e})=>({label:(0,E.jsxs)(E.Fragment,{children:[(0,E.jsx)(s.A,{src:N})," ",(0,D.__)(e,"formgent")]}),value:M})),defaultValue:a,onChange:l}),(0,E.jsxs)(I.AntForm,{form:j,layout:"vertical",onFinish:()=>{(0,n.doAction)("formgent_update_global_settings",t,t,i,"captcha_keys",N,e)},children:[(()=>{const M=`${a}_site_key`,N=`${a}_secret_key`;return(0,E.jsxs)(E.Fragment,{children:[(0,E.jsx)(I.AntFormItem,{label:(0,D.__)("Site key","formgent"),name:M,children:(0,E.jsx)(I.AntInput,{className:"formgent-form-field",placeholder:(0,D.__)("Enter your site key","formgent"),onChange:N=>o(M,N.target.value)})}),(0,E.jsx)(I.AntFormItem,{label:(0,D.__)("Secret key","formgent"),name:N,children:(0,E.jsx)(I.AntInputPassword,{className:"formgent-form-field",placeholder:(0,D.__)("Enter your secret key","formgent"),onChange:M=>o(N,M.target.value),visibilityToggle:!1})})]})})(),(0,E.jsx)("div",{className:"formgent-settings-action",children:(0,E.jsx)(I.AntButton,{type:"primary",htmlType:"submit",children:(0,D.__)("Save","formgent")})})]})]})]})}function TM(){const{enable_honeypot_protection:M,loading:N}=(0,g.useSelect)(M=>{const{getHoneypotProtection:N,isLoading:e}=M(T.A.GLOBAL_SETTINGS);return{enable_honeypot_protection:N(),loading:e()}},[]),{updateEnableHoneypotProtection:e,updateSettings:t}=(0,g.useDispatch)(T.A.GLOBAL_SETTINGS);return(0,E.jsxs)("div",{className:"formgent-settings-content",children:[(0,E.jsx)("div",{className:"formgent-settings-content__head",children:(0,E.jsx)("h4",{className:"formgent-settings-content__title",children:(0,D.__)("Security & Protection","formgent")})}),(0,E.jsx)("div",{className:"formgent-settings-content__item",children:N?(0,E.jsx)(I.AntSkeleton,{active:!0}):(0,E.jsxs)(E.Fragment,{children:[(0,E.jsx)("div",{className:"formgent-settings-content__toggle",children:(0,E.jsx)(I.Toggle,{title:(0,D.__)("Enable Honeypot Protection","formgent"),description:(0,D.__)("Uses hidden fields to catch bots and reduce spam","formgent"),checked:"yes"===M||!0===M,onChange:async function(M){e(M?"yes":"no");try{await t({enable_honeypot_protection:M?"yes":"no"}),(0,n.doAction)("formgent-toast",{message:M?(0,D.__)("Honeypot Protection Enabled","formgent"):(0,D.__)("Honeypot Protection Disabled","formgent"),type:"success"})}catch(M){console.error(M)}}})}),(0,E.jsx)("div",{className:"formgent-settings-content__card formgent-mt-15",children:(0,E.jsx)(iM,{})})]})})]})}const jM=r.Ay.div`
    min-width: 272px !important;
    height: 100%;
    border-radius: 10px 0 0 10px;
    background-color: var( --formgent-color-gray-50 );
    border-right: 1px solid var(--formgent-color-gray-200);
    .formgent-settings-sider__title {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: var(--formgent-color-text);
    }
    .formgent-settings-sider__top {
        padding: 20px;
        border-bottom: 1px solid var(--formgent-color-border-light);
    }
    .formgent-settings-sider__nav {
        display: flex;
        flex-direction: column;
        gap: 6px;
        padding: 8px;
        margin: 0;
        background: transparent;
        .ant-menu-item {
            font-size: 14px;
            font-weight: 500;
            padding: 0 12px !important;
            margin: 0 !important;
            border-radius: 4px;
            color: var(--formgent-color-gray-600);
            text-decoration: none;
            border: none;
            box-shadow: none;
            outline: none;
            transition: all ease .3s;
            width: 100%;
            height: 36px;
            .ant-menu-sub {
                background: transparent;
            }
            svg{
                width: 20px;
                height: 20px;
            }
            svg:not(.formgent-svg-stroke-only){
                g, path{
                    transition: all ease .3s;
                    fill: var(--formgent-color-gray-600);
                }
            }
            svg.formgent-svg-stroke-only{
                path{
                    stroke: var(--formgent-color-gray-600);
                }
            }
            svg.formgent-code-icon path {
                fill: none;
            }
            &.ant-menu-item-selected,
            &:hover {
                color: var(--formgent-color-primary) !important;
                background-color: var(--formgent-color-primary-50);
                font-weight: 600;
                svg:not(.formgent-svg-stroke-only){
                    g, path{
                        fill: var(--formgent-color-primary);
                    }
                }
                svg.formgent-svg-stroke-only{
                    path{
                        stroke: var(--formgent-color-primary);
                    }
                }
                svg.formgent-code-icon path {
                    fill: none;
                }
            }

            a:focus {
                box-shadow: none;
            }
            .ant-menu-title-content{
                margin-left: 10px
            }
        }
        .ant-menu-submenu {
            .ant-menu-submenu-title {
                font-size: 14px;
                font-weight: 600;
                padding: 10px 15px;
                margin: 0 !important;
                border-radius: 8px;
                color: var(--formgent-font-color);
                text-decoration: none;
                border: none;
                box-shadow: none;
                outline: none;
                transition: all ease .3s;
                &:hover {
                    color: var(--formgent-color-dark);
                    background-color: var(--formgent-color-bg-light);
                }
            }
            &.ant-menu-submenu-selected .ant-menu-submenu-title {
                color: var(--formgent-color-dark);
                background-color: var(--formgent-color-bg-light);
            }
            .ant-menu-sub {
                background: transparent;
            }

        }
    }
`;r.Ay.div`
    flex: 1;
    margin: 0;
    border-radius: 5px;
    display: flex;
    flex-direction: column;
    max-height: 100%;
    overflow-y: auto;
    background-color: var(--formgent-color-white);
    font-family: "Inter", sans-serif;
    #form-quiz-settings{
        flex: 1;
        display: flex;
        flex-direction: column;
    }
    /* Scrollbar Style */
    &::-webkit-scrollbar {
        width: 5px;
        scroll-behavior: smooth;
    }
    &::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    &::-webkit-scrollbar-thumb {
        background: var(--formgent-color-bg-deep);
        transition: var(--formgent-transition);
        border-radius: 3px;
    }
    &::-webkit-scrollbar-thumb:hover {
        background: var(--formgent-color-primary);
    }
    & > .ant-row{
        margin-bottom: 10px;
    }
    .formgent-settings-content__text {
        margin: 2px 0;
        font-size: 14px;
        font-weight: 400;
        color: var(--formgent-color-text);
    }
    .formgent-help-text{
        font-size: 13px;
        display: inline-block;
        margin-top: 10px;
        color: var(--formgent-color-gray-500);
    }
    //settings form
    .formgent-settings-form-fields{
        .ant-form-item-label label{
            color: var(--formgent-color-dark);
            font-size: 14px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
        }
        .formgent-input-addon{
            .formgent-space-align-center{
                display: flex;
                align-items: center;
                gap: 4px;
                color: var(--formgent-color-info-500);
                font-family: 'Inter', sans-serif;
                svg{
                    width: 14px;
                    height: 14px;
                    path{
                        stroke: var(--formgent-color-info);
                    }
                }
                .formgent-icon{
                    height: auto;
                    line-height: 0;
                }
            }
        }
        .formgent-vertical-radio-group{
            .ant-radio-group{
                display: flex;
                flex-direction: column;
                gap: 6px;
            }
        }
        .ant-form-item-additional{
            margin-bottom: 20px;
        }
        .formgent-form-settings-more-options{
            color: var(--formgent-color-primary);
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            margin-bottom: 20px;
            text-decoration: underline;
            display: block;
        }
    }
    .formgent-settings-back-btn{
        border-radius: 8px;
        background: var(--formgent-color-gray-100);
        font-size: 14px;
        font-weight: 600;
        color: var(--formgent-color-gray-600);
        svg{
            width: 16px;
            height: 16px;
        }
    }
    .formgent-settings-content__item {
        width: 908px;
        margin: 36px auto;
        @media only screen and (max-width: 1280px){
            width: auto;
            margin: 36px 32px;
        }
        .formgent-settings-toggle{
            max-width: 908px;
        }
        .formgent-settings-content__toggle {
            margin-bottom: 12px;
        }

        &.formgent-settings-content__item--table{
            margin-top: 24px;
        }
    }

    /* Form confirmation */
    .ant-form-item .ant-form-item-control-input-content{
        max-width: 100%;
    }
    .formgent-ant-select{
        width: 100%;
        max-width: 100%;
    }
    .formgent-form-action-btn-wrapper{
        width: 100%;
        max-width: 100%;
        border-top: 1px solid var(--formgent-color-gray-200);
        padding-top: 30px;
    }

    //ant-skeleton style
    & > .ant-skeleton{
        width: 100%;
        max-width: 908px;
        margin: 0 auto;
        padding: 30px 0;
        height: 100%;
    }
`,r.Ay.div`
    width: calc(100% - 272px);
    .formgent-pro-feature-cta{
        padding-top: 100px;
        &:before{
            content: none;
        }
    }
`,r.Ay.div`
    width: calc(100% - 272px);
    .formgent-pro-feature-cta{
        padding-top: 100px;
        &:before{
            content: none;
        }
    }
`,r.Ay.div`
    .formgent-settings-content__inner{
        width: 100%;
    }

    .formgent-iframe {
        padding: 24px;
        background-color: var(--formgent-color-gray-100);
        border-radius: 8px;
        margin: 0 0 25px 0;
        .formgent-code-wrapper {
            padding: 15px;
            background-color: var(--formgent-color-dark);
            margin-bottom: 15px;
            border-radius: 8px;
            color: #ffffff;
        }

        button {
            border: 1px solid #efefef;
        }
    }

    .formgent-divider {
        border: 1px solid var(--formgent-color-gray-100);
    }
    .formgent-settings-content__title{
        font-size: 18px;
        font-weight: 600;
        color: var(--formgent-color-gray-700);
    }

    .formgent-loader {
        animation: spinner 2s ease-in-out infinite;
    }

    @keyframes spinner {
        from {
            transform: rotate(0deg);
        }
        to {
            transform: rotate(360deg);
        }
    }
`,r.Ay.div`
    .formgent-card-info{
        margin-bottom: 20px;
    }
`,r.Ay.div`
    .formgent-sheet-onboarding{
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 56px 56px 74px;
        .formgent-sheet-onboarding-icon{
            width: 152px;
            height: 152px;
            border-radius: 50%;
            background: var(--formgent-color-gray-50);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 24px;
        }
        h2{
            color: var(--formgent-color-dark);
            font-size: 19px;
            font-weight: 600;
            line-height: 1.32;
            margin: 0 0 4px;
        }
        p{
            margin: 0 0 24px;
            color: var(--formgent-color-gray-600);
            font-size: 14px;
            font-weight: 400;
            line-height: 1.57;
        }
    }
`,r.Ay.div`
	flex: 1;
	overflow: auto;
    .required-sign{
        color: var(--ant-form-label-required-mark-color);
    }
	.formgent-edit-sheet-content {
		width: 100%;
		max-width: 908px;
		margin: 0 auto;
        @media only screen and (max-width: 1280px){
            margin: 0 32px 0;
            width: auto;
        }
	}

    .formgent-edit-sheet-mapping-type-wrapper{
        label{
            font-size: 14px;
            font-weight: 600;
            line-height: 1.1;
            color: var(--formgent-color-dark);
        }
    }

    .formgent-form-field-desc{
        color: var(--formgent-color-gray-500);
        font-size: 12px;
        font-weight: 400;
        margin-top: 4px;
        display: block;
    }
    .formgent-edit-sheet-mapping-type{
        h2{
            margin: 0 0 12px 0;
            color: var(--formgent-color-dark);
            font-size: 14px;
            font-weight: 600;
        }
        .ant-form-item{
            margin-bottom: 0;
        }
        .ant-radio-wrapper{
            border-radius: 8px;
            border: 1px solid var(--formgent-color-gray-200);
            background: #fff;
            margin-bottom: 12px;
            width: 100%;
            padding: 11px 16px;
            .ant-radio{
                padding-right: 4px;
                top: -7px;
            }
            h3{
                margin: 0 0 2px;
                color: var(--formgent-color-dark);
                font-size: 12px;
                font-weight: 600;
                line-height: 1.2;
                letter-spacing: 0.12px;
            }
            span{
                color: var(--formgent-color-gray-600);
                font-size: 12px;
                font-weight: 400;
            }
        }
    }
    .formgent-edit-sheet-conditional-sync{
        .formgent-settings-toggle{
            align-items: flex-start;
            .ant-switch{
                position: relative;
                top: 3px;
            }
        }
        .formgent-logic-condition-type{
            .ant-form-item{
                margin-bottom: 0;
            }
            .formgent-ant-select{
                width: 90px;
            }
        }
        .ruleGroup-body{
            margin-bottom: 0;
        }
        .formgent-form-settings-logic .queryBuilder .ruleGroup-body .ant-select-selector,
        .formgent-form-settings-logic .queryBuilder .ruleGroup-body .ant-input{
            border-radius: 6px;
        }
    }
    .ant-form-item:last-child{
        margin-bottom: 0;
    }
    .formgent-ant-select{
        width: 100%;
    }
`,r.Ay.div`
    .ant-form-item{
        margin-bottom: 0;
    }
    .formgent-mapping-table-quiz{
        padding: 0 24px 10px;
    }
    .formgent-mapping-crm-table{
        .formgent-mapping-table-header{
            padding-top: 0;
        }
    }
    .formgent-mapping-table-header{
        display: flex;
        align-items: center;
        padding: 16px 0 24px;
        gap: 12px;
        .formgent-mapping-table-column{
            color: var(--formgent-color-dark);
            font-size: 14px;
            font-weight: 600;
            line-height: 1.1;
            &:first-child{
                width: 40%;
            }
            &:nth-child(2){
                width: 40%;
            }
            &:last-child{
                width: 20%;
            }

            button{
                background: none;
                border: 0 none;
                display: flex;
                align-items: center;
                gap: 6px;
                cursor: pointer;
                transition: 0.3s ease;
                color: var(--formgent-color-primary);
                font-size: 14px;
                font-weight: 500;
                svg{
                    width: 18px;
                    height: 18px;
                    path{
                        stroke: var(--formgent-color-primary);
                    }
                }
            }
            .formgent-ant-input{
                width: 100%;
            }
        }
        .formgent-mapping-table-column--action{
            display: flex;
            align-items: center;
            justify-content: flex-end;
        }
    }
    .formgent-mapping-table-body{
        .formgent-mapping-table-row{
            display: flex;
            align-items: center;
            gap: 12px;
            &:not(:last-child){
                margin-bottom: 24px;
            }
            .formgent-mapping-table-column{
                display: flex;
                align-items: center;
                gap: 14px;
                &:first-child{
                    width: 40%;
                }
                &:nth-child(2){
                    width: 55%;
                }
                &:last-child{
                    width: 5%;
                }
                .formgent-ant-input,
                .ant-input-number{
                    width: 100%;
                }
            }
            .formgent-ant-select{
                width: 100%;
            }
            .formgent-ant-button-wrapper{
                .ant-btn{
                    width: 48px;
                    height: 48px;
                    padding: 0;
                    border-radius: 6px;
                    svg{
                        width: 20px;
                        height: 20px;
                        path{
                            fill: var(--formgent-color-gray-600);
                            transition: 0.3s ease;
                        }
                    }
                    &:disabled{
                        pointer-events: none;
                        svg path{
                            fill: var(--formgent-color-gray-400);
                        }
                    }
                    &:hover{
                        background: var(--formgent-color-danger-100);
                        border-color: var(--formgent-color-danger);
                        svg path{
                            fill: var(--formgent-color-danger);
                        }
                    }
                }
            }
        }
    }
`,r.Ay.div`
    max-width: 908px;
    margin: 36px auto;
    background-color: var(--formgent-color-gray-50);
    background-repeat: no-repeat;
    background-position: center center;
    border-radius: 16px;
    padding: 188px 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    text-align: center;
    @media only screen and (max-width: 1280px){
        margin: 36px 32px;
    }
    h2{
        color: var(--formgent-color-dark);
        font-size: 20px;
        font-weight: 600;
        margin: 16px 0 4px;
    }
    p{
        margin: 0;
        color: var(--formgent-color-gray-600);
        font-size: 14px;
        font-weight: 400;
    }
    .formgent-ant-button-wrapper{
        margin-top: 20px;
    }
`,r.Ay.div`
    max-width: 480px;
    margin: 0 auto;
    padding: 164px 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-direction: column;
    text-align: center;
    @media only screen and (max-width: 1280px){
        margin: 0 32px;
    }
    h2{
        margin: 20px 0 8px;
        color: var(--formgent-color-dark);
        font-size: 20px;
        font-weight: 600;
    }
    p{
        color: var(--formgent-color-gray-600);
        font-size: 14px;
        font-weight: 400;
        margin: 0 0 16px;
    }
`,r.Ay.div`
    .formgent-table-sheet-info-container{
        display: flex;
        align-items: center;
        gap: 12px;
        svg{
            width: 30px;
            height: 30px;
        }
    }
    &.table-mailchimp{
        .formgent-table-sheet-info-content{
            display: flex;
            align-items: center;
            gap: 10px;
        }
    }
    .formgent-table-sheet-info-content{
        h3{
            margin: 0 0 2px 0;
            color: var( --formgent-color-dark );
            font-size: 14px;
            font-weight: 500;
            line-height: normal;
        }
        span{
            color: var(--formgent-color-gray-600);
            font-size: 14px;
            font-weight: 400;
            line-height: 1.43;
            display: block ;
            strong{
                font-weight: 500;
            }
        }
        h3,
        span{
            width: 100%;
            max-width: 280px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    }
    .formgent-ant-button-wrapper{
        .ant-btn{
            background: var(--formgent-color-gray-50);
        }
    }
    .ant-table{
        .formgent-table-sheet-info{
            width: 700px;
        }
    }
    .formgent-table-head-action{
        text-align: right;
    }
`;var aM=e(48147);function cM(M){const{useLocation:N,navItems:e}=M,t=N().pathname.split("settings/")[1],g=M=>{const N=M.replace(/\//g,"");return t===N},n=M=>{for(const N of M){if(g(N.path))return N;if(N.children){const M=n(N.children);if(M)return M}}return null},D=n(e),i=D?.path.replace(/\//g,""),T=D?.path?.split("/")[0];return(0,E.jsxs)(E.Fragment,{children:[(0,E.jsx)(jM,{className:"formgent-settings-sider",children:(0,E.jsx)(I.AntMenu,{defaultSelectedKeys:[i||"general-settings"],defaultOpenKeys:[T],mode:"inline",items:e,className:"formgent-settings-sider__nav"})}),(0,E.jsx)("div",{className:"formgent-editor-wrap__content__main",children:(0,E.jsx)(aM.sv,{})})]})}function lM(){const{validation_messages:M,loading:N}=(0,g.useSelect)(M=>{const{getValidationMessages:N,isLoading:e}=M(T.A.GLOBAL_SETTINGS);return{validation_messages:N(),loading:e()}},[]),{updateValidationMessages:e,updateSettings:t}=(0,g.useDispatch)(T.A.GLOBAL_SETTINGS),[i]=I.AntForm.useForm(),[j,a]=(0,c.useState)({});(0,c.useEffect)(()=>{M&&(a(M),i.setFieldsValue(M))},[M,i]);const l=[{name:"required",title:(0,D.__)("Required Field","formgent"),placeholder:(0,D.__)("This field is required","formgent")},{name:"email",title:(0,D.__)("Email","formgent"),placeholder:(0,D.__)("This field must contain a valid email","formgent")},{name:"number",title:(0,D.__)("Number","formgent"),placeholder:(0,D.__)("This field must contain numeric value","formgent")},{name:"min",title:(0,D.__)("Minimum Value","formgent"),placeholder:(0,D.__)("This value is below the minimum limit","formgent")},{name:"max",title:(0,D.__)("Maximum Value","formgent"),placeholder:(0,D.__)("This value exceeds the maximum limit","formgent")},{name:"confirm",title:(0,D.__)("Confirm Value","formgent"),placeholder:(0,D.__)("Field values do not match","formgent")},{name:"url",title:(0,D.__)("URL Format Validation","formgent"),placeholder:(0,D.__)("Please enter a valid URL","formgent")},{name:"input_mask",title:(0,D.__)("Input Mask Error","formgent"),placeholder:(0,D.__)("Please fill out the field in required format","formgent")},{name:"gdpr",title:(0,D.__)("GDPR Compliance (Checkbox)","formgent"),placeholder:(0,D.__)("You must agree to proceed","formgent")},{name:"character_limit",title:(0,D.__)("Character Limit","formgent"),placeholder:(0,D.__)("Limit is {limit} characters","formgent")}];return(0,E.jsxs)("div",{className:"formgent-settings-content",children:[(0,E.jsx)("div",{className:"formgent-settings-content__head",children:(0,E.jsx)("h4",{className:"formgent-settings-content__title",children:(0,D.__)("Validation Messages","formgent")})}),(0,E.jsx)("div",{className:"formgent-settings-content__item formgent-mb-40",children:N?(0,E.jsx)(I.AntSkeleton,{active:!0}):(0,E.jsxs)(I.AntForm,{form:i,layout:"vertical",className:"formgent-settings-content__validation-messages",onFinish:()=>{!async function(M){(0,n.doAction)("formgent_update_global_settings",M,j,a,"validation_messages",e,t)}(j)},children:[l.map(M=>(0,E.jsx)("div",{className:"formgent-form-group",children:(0,E.jsx)(I.AntFormItem,{label:M.title,name:M.name,rules:[{required:!0,message:(0,D.__)("The field is required","formgent")}],children:(0,E.jsx)(I.AntInput,{className:"formgent-form-field",placeholder:M.placeholder,onChange:N=>{return e=M.name,t=N.target.value,void a(M=>({...M,[e]:t}));var e,t}})})},M.name)),(0,E.jsx)("div",{className:"formgent-form-group",children:(0,E.jsx)(I.AntButton,{type:"primary",className:"formgent-save-settings-btn",htmlType:"submit",children:(0,D.__)("Save Changes","formgent")})})]})})]})}function IM(){const{CommonReducer:M}=(0,g.useSelect)(M=>M("formgent").getCommonState(),[]);(0,g.useSelect)(M=>{M(T.A.GLOBAL_SETTINGS).getSettings()},[]),(0,n.hasAction)("formgent_update_global_settings","formgent")||(0,n.addAction)("formgent_update_global_settings","formgent",async(M,N,e,t,g,i)=>{const T={...N,...M};e(T);try{await i({[t]:T}),g(T),(0,n.doAction)("formgent-toast",{message:(0,D.__)((0,D.__)("Settings have been updated successfully.","formgent"),"formgent"),type:"success"})}catch(M){console.error(M)}});const{Route:N,Routes:e,NavLink:c,useNavigate:l,useLocation:I}=M.routerComponents,o=(0,n.applyFilters)("formgent_global_settings_routes",[{key:"general-settings",label:(0,E.jsx)(aM.N_,{to:"/settings",children:(0,D.__)("General Settings","formgent")}),icon:(0,E.jsx)(s.A,{src:"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxOCIgaGVpZ2h0PSIxOCIgdmlld0JveD0iMCAwIDE4IDE4IiBmaWxsPSJub25lIj4KICA8ZyBjbGlwLXBhdGg9InVybCgjY2xpcDBfMTE4OV8xODMxNSkiPgogICAgPHBhdGggZD0iTTkgNkM4LjQwNjY2IDYgNy44MjY2NCA2LjE3NTk1IDcuMzMzMjkgNi41MDU1OUM2LjgzOTk0IDYuODM1MjQgNi40NTU0MyA3LjMwMzc3IDYuMjI4MzYgNy44NTE5NUM2LjAwMTMgOC40MDAxMyA1Ljk0MTg5IDkuMDAzMzMgNi4wNTc2NSA5LjU4NTI3QzYuMTczNCAxMC4xNjcyIDYuNDU5MTIgMTAuNzAxOCA2Ljg3ODY4IDExLjEyMTNDNy4yOTgyNCAxMS41NDA5IDcuODMyNzkgMTEuODI2NiA4LjQxNDczIDExLjk0MjRDOC45OTY2NyAxMi4wNTgxIDkuNTk5ODcgMTEuOTk4NyAxMC4xNDgxIDExLjc3MTZDMTAuNjk2MiAxMS41NDQ2IDExLjE2NDggMTEuMTYwMSAxMS40OTQ0IDEwLjY2NjdDMTEuODI0MSAxMC4xNzM0IDEyIDkuNTkzMzQgMTIgOUMxMiA4LjIwNDM1IDExLjY4MzkgNy40NDEyOSAxMS4xMjEzIDYuODc4NjhDMTAuNTU4NyA2LjMxNjA3IDkuNzk1NjUgNiA5IDZaTTkgMTAuNUM4LjcwMzMzIDEwLjUgOC40MTMzMiAxMC40MTIgOC4xNjY2NSAxMC4yNDcyQzcuOTE5OTcgMTAuMDgyNCA3LjcyNzcxIDkuODQ4MTEgNy42MTQxOCA5LjU3NDAyQzcuNTAwNjUgOS4yOTk5NCA3LjQ3MDk1IDguOTk4MzQgNy41Mjg4MiA4LjcwNzM2QzcuNTg2NyA4LjQxNjM5IDcuNzI5NTYgOC4xNDkxMiA3LjkzOTM0IDcuOTM5MzRDOC4xNDkxMiA3LjcyOTU2IDguNDE2MzkgNy41ODY3IDguNzA3MzcgNy41Mjg4MkM4Ljk5ODM0IDcuNDcwOTQgOS4yOTk5NCA3LjUwMDY1IDkuNTc0MDMgNy42MTQxOEM5Ljg0ODEyIDcuNzI3NzEgMTAuMDgyNCA3LjkxOTk3IDEwLjI0NzIgOC4xNjY2NEMxMC40MTIgOC40MTMzMiAxMC41IDguNzAzMzMgMTAuNSA5QzEwLjUgOS4zOTc4MiAxMC4zNDIgOS43NzkzNiAxMC4wNjA3IDEwLjA2MDdDOS43NzkzNiAxMC4zNDIgOS4zOTc4MyAxMC41IDkgMTAuNVoiIGZpbGw9ImN1cnJlbnRDb2xvciIvPgogICAgPHBhdGggZD0iTTE1Ljk3MDYgMTAuNDI1TDE1LjYzNzYgMTAuMjMzQzE1Ljc4NzUgOS40MTczMyAxNS43ODc1IDguNTgxMTcgMTUuNjM3NiA3Ljc2NTVMMTUuOTcwNiA3LjU3MzVDMTYuMjI2NyA3LjQyNTc2IDE2LjQ1MTEgNy4yMjkwMyAxNi42MzEyIDYuOTk0NTVDMTYuODExMiA2Ljc2MDA2IDE2Ljk0MzMgNi40OTI0IDE3LjAxOTkgNi4yMDY4NkMxNy4wOTY2IDUuOTIxMzIgMTcuMTE2MiA1LjYyMzQ5IDE3LjA3NzcgNS4zMzAzN0MxNy4wMzkyIDUuMDM3MjUgMTYuOTQzMyA0Ljc1NDU4IDE2Ljc5NTYgNC40OTg1QzE2LjY0NzkgNC4yNDI0MiAxNi40NTExIDQuMDE3OTUgMTYuMjE2NiAzLjgzNzlDMTUuOTgyMiAzLjY1Nzg1IDE1LjcxNDUgMy41MjU3NSAxNS40MjkgMy40NDkxNUMxNS4xNDM0IDMuMzcyNTQgMTQuODQ1NiAzLjM1MjkyIDE0LjU1MjUgMy4zOTE0MkMxNC4yNTkzIDMuNDI5OTEgMTMuOTc2NyAzLjUyNTc2IDEzLjcyMDYgMy42NzM1TDEzLjM4NjggMy44NjYyNUMxMi43NTY1IDMuMzI3NjkgMTIuMDMyMSAyLjkxMDE5IDExLjI1MDEgMi42MzQ3NVYyLjI1QzExLjI1MDEgMS42NTMyNiAxMS4wMTMgMS4wODA5NyAxMC41OTExIDAuNjU5MDFDMTAuMTY5MSAwLjIzNzA1MyA5LjU5NjgzIDAgOS4wMDAwOSAwQzguNDAzMzYgMCA3LjgzMTA2IDAuMjM3MDUzIDcuNDA5MSAwLjY1OTAxQzYuOTg3MTUgMS4wODA5NyA2Ljc1MDA5IDEuNjUzMjYgNi43NTAwOSAyLjI1VjIuNjM0NzVDNS45NjgxMyAyLjkxMTE4IDUuMjQzOTggMy4zMjk3IDQuNjE0MDkgMy44NjkyNUw0LjI3ODg0IDMuNjc1QzMuNzYxNjcgMy4zNzY2MyAzLjE0NzE1IDMuMjk1OTMgMi41NzA0OCAzLjQ1MDY1QzEuOTkzODEgMy42MDUzNiAxLjUwMjIxIDMuOTgyODMgMS4yMDM4NCA0LjVDMC45MDU0NzUgNS4wMTcxNyAwLjgyNDc3MiA1LjYzMTY5IDAuOTc5NDkgNi4yMDgzNkMxLjEzNDIxIDYuNzg1MDQgMS41MTE2NyA3LjI3NjYzIDIuMDI4ODQgNy41NzVMMi4zNjE4NCA3Ljc2N0MyLjIxMTkzIDguNTgyNjcgMi4yMTE5MyA5LjQxODgzIDIuMzYxODQgMTAuMjM0NUwyLjAyODg0IDEwLjQyNjVDMS41MTE2NyAxMC43MjQ5IDEuMTM0MjEgMTEuMjE2NSAwLjk3OTQ5IDExLjc5MzFDMC44MjQ3NzIgMTIuMzY5OCAwLjkwNTQ3NSAxMi45ODQzIDEuMjAzODQgMTMuNTAxNUMxLjUwMjIxIDE0LjAxODcgMS45OTM4MSAxNC4zOTYxIDIuNTcwNDggMTQuNTUwOUMzLjE0NzE1IDE0LjcwNTYgMy43NjE2NyAxNC42MjQ5IDQuMjc4ODQgMTQuMzI2NUw0LjYxMjU5IDE0LjEzMzdDNS4yNDMxNyAxNC42NzI0IDUuOTY3ODMgMTUuMDg5OSA2Ljc1MDA5IDE1LjM2NTJWMTUuNzVDNi43NTAwOSAxNi4zNDY3IDYuOTg3MTUgMTYuOTE5IDcuNDA5MSAxNy4zNDFDNy44MzEwNiAxNy43NjI5IDguNDAzMzYgMTggOS4wMDAwOSAxOEM5LjU5NjgzIDE4IDEwLjE2OTEgMTcuNzYyOSAxMC41OTExIDE3LjM0MUMxMS4wMTMgMTYuOTE5IDExLjI1MDEgMTYuMzQ2NyAxMS4yNTAxIDE1Ljc1VjE1LjM2NTJDMTIuMDMyMSAxNS4wODg4IDEyLjc1NjIgMTQuNjcwMyAxMy4zODYxIDE0LjEzMDdMMTMuNzIxMyAxNC4zMjQzQzE0LjIzODUgMTQuNjIyNiAxNC44NTMgMTQuNzAzMyAxNS40Mjk3IDE0LjU0ODZDMTYuMDA2NCAxNC4zOTM5IDE2LjQ5OCAxNC4wMTY0IDE2Ljc5NjMgMTMuNDk5M0MxNy4wOTQ3IDEyLjk4MjEgMTcuMTc1NCAxMi4zNjc2IDE3LjAyMDcgMTEuNzkwOUMxNi44NjYgMTEuMjE0MiAxNi40ODg1IDEwLjcyMjYgMTUuOTcxMyAxMC40MjQyTDE1Ljk3MDYgMTAuNDI1Wk0xNC4wNTk2IDcuNTkzQzE0LjMxMzYgOC41MTMzIDE0LjMxMzYgOS40ODUyIDE0LjA1OTYgMTAuNDA1NUMxNC4wMTUyIDEwLjU2NTcgMTQuMDI1NCAxMC43MzYxIDE0LjA4ODMgMTAuODg5OUMxNC4xNTEzIDExLjA0MzcgMTQuMjYzNiAxMS4xNzIyIDE0LjQwNzYgMTEuMjU1MkwxNS4yMjA2IDExLjcyNDhDMTUuMzkzIDExLjgyNDIgMTUuNTE4NyAxMS45ODgxIDE1LjU3MDMgMTIuMTgwM0MxNS42MjE4IDEyLjM3MjUgMTUuNTk0OSAxMi41NzczIDE1LjQ5NTUgMTIuNzQ5NkMxNS4zOTYgMTIuOTIyIDE1LjIzMjIgMTMuMDQ3OCAxNS4wNCAxMy4wOTkzQzE0Ljg0NzggMTMuMTUwOSAxNC42NDMgMTMuMTI0IDE0LjQ3MDYgMTMuMDI0NUwxMy42NTYxIDEyLjU1MzVDMTMuNTEyIDEyLjQ3MDEgMTMuMzQ0MyAxMi40MzY5IDEzLjE3OTMgMTIuNDU5M0MxMy4wMTQzIDEyLjQ4MTcgMTIuODYxNSAxMi41NTg0IDEyLjc0NDggMTIuNjc3MkMxMi4wNzczIDEzLjM1ODcgMTEuMjM2MyAxMy44NDUgMTAuMzEyNiAxNC4wODM1QzEwLjE1MTQgMTQuMTI1IDEwLjAwODUgMTQuMjE4OSA5LjkwNjU0IDE0LjM1MDRDOS44MDQ1NiAxNC40ODIgOS43NDkyNiAxNC42NDM4IDkuNzQ5MzQgMTQuODEwMlYxNS43NUM5Ljc0OTM0IDE1Ljk0ODkgOS42NzAzMyAxNi4xMzk3IDkuNTI5NjcgMTYuMjgwM0M5LjM4OTAyIDE2LjQyMSA5LjE5ODI2IDE2LjUgOC45OTkzNCAxNi41QzguODAwNDMgMTYuNSA4LjYwOTY3IDE2LjQyMSA4LjQ2OTAxIDE2LjI4MDNDOC4zMjgzNiAxNi4xMzk3IDguMjQ5MzQgMTUuOTQ4OSA4LjI0OTM0IDE1Ljc1VjE0LjgxMUM4LjI0OTQzIDE0LjY0NDUgOC4xOTQxMiAxNC40ODI4IDguMDkyMTUgMTQuMzUxMkM3Ljk5MDE3IDE0LjIxOTYgNy44NDczMiAxNC4xMjU3IDcuNjg2MDkgMTQuMDg0MkM2Ljc2MjM1IDEzLjg0NDggNS45MjE1OSAxMy4zNTc1IDUuMjU0NTkgMTIuNjc1QzUuMTM3OTkgMTIuNTU2MSA0Ljk4NTE1IDEyLjQ3OTUgNC44MjAxNiAxMi40NTcxQzQuNjU1MTcgMTIuNDM0NyA0LjQ4NzQyIDEyLjQ2NzggNC4zNDMzNCAxMi41NTEzTDMuNTMwMzQgMTMuMDIxNUMzLjQ0NTAxIDEzLjA3MTUgMy4zNTA2NCAxMy4xMDQyIDMuMjUyNjQgMTMuMTE3NkMzLjE1NDY0IDEzLjEzMSAzLjA1NDk2IDEzLjEyNDkgMi45NTkzMyAxMy4wOTk2QzIuODYzNzEgMTMuMDc0MyAyLjc3NDAzIDEzLjAzMDMgMi42OTU0NiAxMi45NzAzQzIuNjE2ODkgMTIuOTEwMiAyLjU1MDk4IDEyLjgzNTIgMi41MDE1MiAxMi43NDk1QzIuNDUyMDYgMTIuNjYzOCAyLjQyMDA0IDEyLjU2OTIgMi40MDcyOSAxMi40NzEyQzIuMzk0NTQgMTIuMzczMSAyLjQwMTMyIDEyLjI3MzQgMi40MjcyNCAxMi4xNzhDMi40NTMxNSAxMi4wODI1IDIuNDk3NjkgMTEuOTkzMSAyLjU1ODI5IDExLjkxNUMyLjYxODg5IDExLjgzNjggMi42OTQzNiAxMS43NzE0IDIuNzgwMzQgMTEuNzIyNUwzLjU5MzM0IDExLjI1M0MzLjczNzMgMTEuMTY5OSAzLjg0OTYgMTEuMDQxNCAzLjkxMjU5IDEwLjg4NzZDMy45NzU1OCAxMC43MzM4IDMuOTg1NjkgMTAuNTYzNCAzLjk0MTM0IDEwLjQwMzJDMy42ODczNiA5LjQ4Mjk1IDMuNjg3MzYgOC41MTEwNSAzLjk0MTM0IDcuNTkwNzVDMy45ODQ4OSA3LjQzMDkxIDMuOTc0MyA3LjI2MTE1IDMuOTExMiA3LjEwNzk3QzMuODQ4MTEgNi45NTQ3OSAzLjczNjA4IDYuODI2OCAzLjU5MjU5IDYuNzQ0TDIuNzc5NTkgNi4yNzQ1QzIuNjA3MjQgNi4xNzUwNCAyLjQ4MTQ1IDYuMDExMTkgMi40Mjk5IDUuODE4OTlDMi4zNzgzNSA1LjYyNjc5IDIuNDA1MjYgNS40MjE5OCAyLjUwNDcyIDUuMjQ5NjJDMi42MDQxNyA1LjA3NzI3IDIuNzY4MDMgNC45NTE0OCAyLjk2MDIzIDQuODk5OTNDMy4xNTI0MyA0Ljg0ODM4IDMuMzU3MjQgNC44NzUyOSAzLjUyOTU5IDQuOTc0NzVMNC4zNDQwOSA1LjQ0NTc1QzQuNDg3NzggNS41MjkzOSA0LjY1NTE4IDUuNTYyOTEgNC44MTk5OSA1LjU0MTA1QzQuOTg0OCA1LjUxOTE5IDUuMTM3NjggNS40NDMyIDUuMjU0NTkgNS4zMjVDNS45MjIxNiA0LjY0MzUxIDYuNzYzMTYgNC4xNTcyNyA3LjY4Njg0IDMuOTE4NzVDNy44NDg1NyAzLjg3NzE3IDcuOTkxNzkgMy43ODI4MSA4LjA5MzgyIDMuNjUwNjNDOC4xOTU4NSAzLjUxODQ0IDguMjUwODQgMy4zNTU5OCA4LjI1MDA5IDMuMTg5VjIuMjVDOC4yNTAwOSAyLjA1MTA5IDguMzI5MTEgMS44NjAzMiA4LjQ2OTc2IDEuNzE5NjdDOC42MTA0MiAxLjU3OTAyIDguODAxMTggMS41IDkuMDAwMDkgMS41QzkuMTk5MDEgMS41IDkuMzg5NzcgMS41NzkwMiA5LjUzMDQyIDEuNzE5NjdDOS42NzEwOCAxLjg2MDMyIDkuNzUwMDkgMi4wNTEwOSA5Ljc1MDA5IDIuMjVWMy4xODlDOS43NTAwMSAzLjM1NTQ3IDkuODA1MzEgMy41MTcyMyA5LjkwNzI5IDMuNjQ4ODFDMTAuMDA5MyAzLjc4MDM5IDEwLjE1MjEgMy44NzQzIDEwLjMxMzMgMy45MTU3NUMxMS4yMzc0IDQuMTU1MTEgMTIuMDc4NCA0LjY0MjQxIDEyLjc0NTYgNS4zMjVDMTIuODYyMiA1LjQ0Mzg1IDEzLjAxNSA1LjUyMDUyIDEzLjE4IDUuNTQyOTJDMTMuMzQ1IDUuNTY1MzMgMTMuNTEyOCA1LjUzMjIgMTMuNjU2OCA1LjQ0ODc1TDE0LjQ2OTggNC45Nzg1QzE0LjU1NTIgNC45Mjg0OCAxNC42NDk2IDQuODk1ODMgMTQuNzQ3NiA0Ljg4MjQzQzE0Ljg0NTUgNC44NjkwMyAxNC45NDUyIDQuODc1MTQgMTUuMDQwOSA0LjkwMDQzQzE1LjEzNjUgNC45MjU3MSAxNS4yMjYyIDQuOTY5NjUgMTUuMzA0NyA1LjAyOTc0QzE1LjM4MzMgNS4wODk4MiAxNS40NDkyIDUuMTY0ODUgMTUuNDk4NyA1LjI1MDVDMTUuNTQ4MSA1LjMzNjE2IDE1LjU4MDEgNS40MzA3NiAxNS41OTI5IDUuNTI4ODRDMTUuNjA1NiA1LjYyNjkzIDE1LjU5ODkgNS43MjY1NiAxNS41NzMgNS44MjIwMkMxNS41NDcgNS45MTc0NyAxNS41MDI1IDYuMDA2ODYgMTUuNDQxOSA2LjA4NTAzQzE1LjM4MTMgNi4xNjMyIDE1LjMwNTggNi4yMjg2MSAxNS4yMTk4IDYuMjc3NUwxNC40MDY4IDYuNzQ3QzE0LjI2MzYgNi44MzAwMyAxNC4xNTE5IDYuOTU4MTEgMTQuMDg5MSA3LjExMTI3QzE0LjAyNjMgNy4yNjQ0MyAxNC4wMTU5IDcuNDM0MDcgMTQuMDU5NiA3LjU5Mzc1VjcuNTkzWiIgZmlsbD0iY3VycmVudENvbG9yIi8+CiAgPC9nPgogIDxkZWZzPgogICAgPGNsaXBQYXRoIGlkPSJjbGlwMF8xMTg5XzE4MzE1Ij4KICAgICAgPHJlY3Qgd2lkdGg9IjE4IiBoZWlnaHQ9IjE4IiBmaWxsPSJ3aGl0ZSIvPgogICAgPC9jbGlwUGF0aD4KICA8L2RlZnM+Cjwvc3ZnPg=="}),path:"/",element:(0,E.jsx)($,{})},{key:"security-settings",label:(0,E.jsx)(aM.N_,{to:"/settings/security-settings",children:(0,D.__)("Security & Protection","formgent")}),icon:(0,E.jsx)(s.A,{src:"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2IiBmaWxsPSJub25lIj4KICA8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTcuODA5NDkgMC43MjU2MkM3LjkzNTk2IDAuNzA3MzQ1IDguMDY0NCAwLjcwNzM0NSA4LjE5MDg2IDAuNzI1NjJDOC4zMzY2MiAwLjc0NjY4MiA4LjQ3MjI1IDAuNzk3OTE5IDguNTc5OTQgMC44Mzg2MDNDOC41ODk3OSAwLjg0MjMyNiA4LjU5OTQyIDAuODQ1OTYxIDguNjA4NzkgMC44NDk0NzdMMTIuMTgzMyAyLjE4OTkzQzEyLjIxMjYgMi4yMDA5IDEyLjI0MTYgMi4yMTE3NCAxMi4yNzA0IDIuMjIyNDlDMTIuNjgyMyAyLjM3NjI2IDEzLjA0NCAyLjUxMTMgMTMuMzIyNSAyLjc1Njc4QzEzLjU2NjIgMi45NzE1MiAxMy43NTM5IDMuMjQyMzkgMTMuODY5NCAzLjU0NTk1QzE0LjAwMTUgMy44OTI5NiAxNC4wMDA5IDQuMjc5MDQgMTQuMDAwMyA0LjcxODY2QzE0LjAwMDIgNC43NDkzOSAxNC4wMDAyIDQuNzgwMzkgMTQuMDAwMiA0LjgxMTY1VjcuOTk5OTFDMTQuMDAwMiA5Ljg4NDMzIDEyLjk3NTMgMTEuNDU1NCAxMS44Njc1IDEyLjYwOTVDMTAuNzUyMiAxMy43NzE0IDkuNDc2MTggMTQuNTkyIDguODAxNDQgMTQuOTg1N0M4Ljc5MjU0IDE0Ljk5MDkgOC43ODM0NCAxNC45OTYyIDguNzc0MTMgMTUuMDAxN0M4LjY1MSAxNS4wNzQgOC40OTExOCAxNS4xNjc4IDguMjc5NzUgMTUuMjEzMkM4LjEwNjc4IDE1LjI1MDMgNy44OTM1NyAxNS4yNTAzIDcuNzIwNiAxNS4yMTMyQzcuNTA5MTcgMTUuMTY3OCA3LjM0OTM1IDE1LjA3NCA3LjIyNjIyIDE1LjAwMTdDNy4yMTY5MSAxNC45OTYyIDcuMjA3ODEgMTQuOTkwOSA3LjE5ODkxIDE0Ljk4NTdDNi41MjQxNyAxNC41OTIgNS4yNDgxNSAxMy43NzE0IDQuMTMyODggMTIuNjA5NUMzLjAyNTA0IDExLjQ1NTQgMi4wMDAxOCA5Ljg4NDMzIDIuMDAwMTggNy45OTk5MVY0LjgxMTY1QzIuMDAwMTggNC43ODAzOSAyLjAwMDEzIDQuNzQ5NCAyLjAwMDA5IDQuNzE4NjdDMS45OTk0NSA0LjI3OTA0IDEuOTk4ODkgMy44OTI5NiAyLjEzMDk0IDMuNTQ1OTVDMi4yNDY0NSAzLjI0MjM5IDIuNDM0MTYgMi45NzE1MiAyLjY3NzgzIDIuNzU2NzhDMi45NTYzOCAyLjUxMTMgMy4zMTgwOCAyLjM3NjI2IDMuNzI5OTQgMi4yMjI0OUMzLjc1ODczIDIuMjExNzQgMy43ODc3NiAyLjIwMDkgMy44MTcwMyAyLjE4OTkzTDcuMzkxNTYgMC44NDk0NzdDNy40MDA5NCAwLjg0NTk2MSA3LjQxMDU2IDAuODQyMzI2IDcuNDIwNDIgMC44Mzg2MDNDNy41MjgxMSAwLjc5NzkxOSA3LjY2MzczIDAuNzQ2NjgyIDcuODA5NDkgMC43MjU2MlpNNy45OTgxNiAyLjA0NzQzQzcuOTcwOTMgMi4wNTYzNiA3LjkzNCAyLjA3MDA2IDcuODU5NzMgMi4wOTc5Mkw0LjI4NTIgMy40MzgzN0MzLjczNTA0IDMuNjQ0NjggMy42MjkxNiAzLjY5NTYxIDMuNTU5NCAzLjc1NzA5QzMuNDc4MTcgMy44Mjg2NyAzLjQxNTYgMy45MTg5NiAzLjM3NzEgNC4wMjAxNUMzLjM0NDAzIDQuMTA3MDYgMy4zMzM1MSA0LjIyNDA4IDMuMzMzNTEgNC44MTE2NVY3Ljk5OTkxQzMuMzMzNTEgOS4zODc3OSA0LjA5MzMgMTAuNjQyOSA1LjA5NDc5IDExLjY4NjJDNi4wODg4NSAxMi43MjE4IDcuMjQ2ODMgMTMuNDcgNy44NzA3OSAxMy44MzRDNy45MDg5MyAxMy44NTYyIDcuOTM2NDQgMTMuODcyMyA3Ljk2MDM1IDEzLjg4NTdDNy45NzkxMiAxMy44OTYzIDcuOTkxNjEgMTMuOTAzIDguMDAwMTggMTMuOTA3M0M4LjAwODc0IDEzLjkwMyA4LjAyMTIzIDEzLjg5NjMgOC4wNCAxMy44ODU3QzguMDYzOTIgMTMuODcyMyA4LjA5MTQzIDEzLjg1NjIgOC4xMjk1NiAxMy44MzRDOC43NTM1MyAxMy40NyA5LjkxMTUxIDEyLjcyMTggMTAuOTA1NiAxMS42ODYyQzExLjkwNzEgMTAuNjQyOSAxMi42NjY4IDkuMzg3NzkgMTIuNjY2OCA3Ljk5OTkxVjQuODExNjVDMTIuNjY2OCA0LjIyNDA4IDEyLjY1NjMgNC4xMDcwNiAxMi42MjMzIDQuMDIwMTVDMTIuNTg0OCAzLjkxODk2IDEyLjUyMjIgMy44Mjg2NyAxMi40NDEgMy43NTcwOUMxMi4zNzEyIDMuNjk1NjEgMTIuMjY1MyAzLjY0NDY4IDExLjcxNTIgMy40MzgzN0w4LjE0MDYzIDIuMDk3OTJDOC4wNjYzNSAyLjA3MDA2IDguMDI5NDIgMi4wNTYzNiA4LjAwMjE5IDIuMDQ3NDNDOC4wMDE0OSAyLjA0NzIgOC4wMDA4MiAyLjA0Njk4IDguMDAwMTggMi4wNDY3N0M3Ljk5OTUzIDIuMDQ2OTggNy45OTg4NiAyLjA0NzIgNy45OTgxNiAyLjA0NzQzWk0xMC44MDQ5IDUuNTI4NTFDMTEuMDY1MyA1Ljc4ODg2IDExLjA2NTMgNi4yMTA5NyAxMC44MDQ5IDYuNDcxMzJMNy44MDQ5MSA5LjQ3MTMyQzcuNTQ0NTYgOS43MzE2NyA3LjEyMjQ1IDkuNzMxNjcgNi44NjIxIDkuNDcxMzJMNS41Mjg3NyA4LjEzNzk4QzUuMjY4NDIgNy44Nzc2NCA1LjI2ODQyIDcuNDU1NTMgNS41Mjg3NyA3LjE5NTE4QzUuNzg5MTIgNi45MzQ4MyA2LjIxMTIzIDYuOTM0ODMgNi40NzE1OCA3LjE5NTE4TDcuMzMzNTEgOC4wNTcxMUw5Ljg2MjExIDUuNTI4NTFDMTAuMTIyNSA1LjI2ODE2IDEwLjU0NDYgNS4yNjgxNiAxMC44MDQ5IDUuNTI4NTFaIiBmaWxsPSIjNzQ3Qzg5Ii8+Cjwvc3ZnPg=="}),path:"/security-settings",element:(0,E.jsx)(TM,{})},{key:"validation-settings",label:(0,E.jsx)(aM.N_,{to:"/settings/validation-settings",children:(0,D.__)("Validation","formgent")}),icon:(0,E.jsx)(s.A,{src:"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2IiBmaWxsPSJub25lIj4KICA8ZyBjbGlwLXBhdGg9InVybCgjY2xpcDBfNDk1MV82MzI1KSI+CiAgICA8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTcuOTk5ODQgMS45OTk4NEM0LjY4NjEzIDEuOTk5ODQgMS45OTk4NCA0LjY4NjEzIDEuOTk5ODQgNy45OTk4NEMxLjk5OTg0IDExLjMxMzUgNC42ODYxMyAxMy45OTk4IDcuOTk5ODQgMTMuOTk5OEMxMS4zMTM1IDEzLjk5OTggMTMuOTk5OCAxMS4zMTM1IDEzLjk5OTggNy45OTk4NEMxMy45OTk4IDQuNjg2MTMgMTEuMzEzNSAxLjk5OTg0IDcuOTk5ODQgMS45OTk4NFpNMC42NjY1MDQgNy45OTk4NEMwLjY2NjUwNCAzLjk0OTc1IDMuOTQ5NzUgMC42NjY1MDQgNy45OTk4NCAwLjY2NjUwNEMxMi4wNDk5IDAuNjY2NTA0IDE1LjMzMzIgMy45NDk3NSAxNS4zMzMyIDcuOTk5ODRDMTUuMzMzMiAxMi4wNDk5IDEyLjA0OTkgMTUuMzMzMiA3Ljk5OTg0IDE1LjMzMzJDMy45NDk3NSAxNS4zMzMyIDAuNjY2NTA0IDEyLjA0OTkgMC42NjY1MDQgNy45OTk4NFpNMTEuNDcxMiA1LjUyODQzQzExLjczMTYgNS43ODg3OCAxMS43MzE2IDYuMjEwODkgMTEuNDcxMiA2LjQ3MTI0TDcuNDcxMjQgMTAuNDcxMkM3LjIxMDg5IDEwLjczMTYgNi43ODg3OCAxMC43MzE2IDYuNTI4NDMgMTAuNDcxMkw0LjUyODQzIDguNDcxMjRDNC4yNjgwOCA4LjIxMDg5IDQuMjY4MDggNy43ODg3OCA0LjUyODQzIDcuNTI4NDNDNC43ODg3OCA3LjI2ODA4IDUuMjEwODkgNy4yNjgwOCA1LjQ3MTI0IDcuNTI4NDNMNi45OTk4NCA5LjA1NzAzTDEwLjUyODQgNS41Mjg0M0MxMC43ODg4IDUuMjY4MDggMTEuMjEwOSA1LjI2ODA4IDExLjQ3MTIgNS41Mjg0M1oiIGZpbGw9IiM3NDdDODkiLz4KICA8L2c+CiAgPGRlZnM+CiAgICA8Y2xpcFBhdGggaWQ9ImNsaXAwXzQ5NTFfNjMyNSI+CiAgICAgIDxyZWN0IHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgZmlsbD0id2hpdGUiLz4KICAgIDwvY2xpcFBhdGg+CiAgPC9kZWZzPgo8L3N2Zz4="}),path:"/validation-settings",element:(0,E.jsx)(lM,{})},{key:"integrations",label:(0,E.jsx)(aM.N_,{to:"/settings/integrations",children:(0,D.__)("Integrations","formgent")}),icon:(0,E.jsx)(s.A,{src:"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2IiBmaWxsPSJub25lIj4KICA8ZyBjbGlwLXBhdGg9InVybCgjY2xpcDBfNzQ3Ml8xNTE1KSI+CiAgICA8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTcuNTI3OTQgMC44NjIwMUM3Ljc4ODI5IDAuNjAxNjYxIDguMjEwNCAwLjYwMTY2MSA4LjQ3MDc1IDAuODYyMDFMMTAuMjE3MSAyLjYwODM4QzEwLjYzODcgMS45NTIyMiAxMS4xNzgzIDEuNTM4MSAxMS43OTI1IDEuMzgyMDdDMTIuNjM2OSAxLjE2NzU1IDEzLjQ0NzEgMS40ODgzNSAxMy45NzkxIDIuMDIwMzRDMTQuNTExMSAyLjU1MjM0IDE0LjgzMTkgMy4zNjI1OCAxNC42MTc0IDQuMjA2OTRDMTQuNDYxMyA0LjgyMTExIDE0LjA0NzIgNS4zNjA3NSAxMy4zOTEgNS43ODIzMUwxNS4xMzc0IDcuNTI4NjhDMTUuMzk3OCA3Ljc4OTAzIDE1LjM5NzggOC4yMTExNCAxNS4xMzc0IDguNDcxNDlMMTIuNzM3NCAxMC44NzE1QzEyLjU3NzYgMTEuMDMxMyAxMi4zNDc2IDExLjA5OTMgMTIuMTI2NiAxMS4wNTJDMTEuOTA1NSAxMS4wMDQ3IDExLjcyMzUgMTAuODQ4NyAxMS42NDMgMTAuNjM3NEMxMS4yOTE1IDkuNzE0NzIgMTAuODQ0NiA5LjQxNzIzIDEwLjU0NDYgOS4zNDEwMUMxMC4yMzI3IDkuMjYxNzggOS44ODg3NiA5LjM3MDE1IDkuNjI5MDkgOS42Mjk4MkM5LjM2OTQxIDkuODg5NDkgOS4yNjEwNSAxMC4yMzM0IDkuMzQwMjggMTAuNTQ1M0M5LjQxNjUgMTAuODQ1MyA5LjcxMzk4IDExLjI5MjMgMTAuNjM2NyAxMS42NDM4QzEwLjg0NzkgMTEuNzI0MiAxMS4wMDQgMTEuOTA2MyAxMS4wNTEzIDEyLjEyNzNDMTEuMDk4NSAxMi4zNDg0IDExLjAzMDYgMTIuNTc4MyAxMC44NzA4IDEyLjczODJMOC40NzA3NSAxNS4xMzgyQzguMjEwNCAxNS4zOTg1IDcuNzg4MjkgMTUuMzk4NSA3LjUyNzk0IDE1LjEzODJMNS43ODE1NyAxMy4zOTE4QzUuMzYwMDIgMTQuMDQ3OSA0LjgyMDM4IDE0LjQ2MjEgNC4yMDYyMSAxNC42MTgxQzMuMzYxODQgMTQuODMyNiAyLjU1MTYxIDE0LjUxMTggMi4wMTk2MSAxMy45Nzk4QzEuNDg3NjIgMTMuNDQ3OCAxLjE2NjgyIDEyLjYzNzYgMS4zODEzMyAxMS43OTMyQzEuNTM3MzcgMTEuMTc5IDEuOTUxNDkgMTAuNjM5NCAyLjYwNzY1IDEwLjIxNzlMMC44NjEyNzggOC40NzE0OUMwLjYwMDkyOCA4LjIxMTE0IDAuNjAwOTI4IDcuNzg5MDMgMC44NjEyNzggNy41Mjg2OEwzLjI2MTI4IDUuMTI4NjhDMy40MjExMiA0Ljk2ODg0IDMuNjUxMDcgNC45MDA4OCAzLjg3MjEyIDQuOTQ4MTZDNC4wOTMxNyA0Ljk5NTQ0IDQuMjc1MiA1LjE1MTUxIDQuMzU1NjcgNS4zNjI3NUM0LjcwNzE4IDYuMjg1NDQgNS4xNTQxMyA2LjU4MjkzIDUuNDU0MTMgNi42NTkxNUM1Ljc2NjAxIDYuNzM4MzggNi4xMDk5NCA2LjYzMDAxIDYuMzY5NjEgNi4zNzAzNEM2LjYyOTI4IDYuMTEwNjcgNi43Mzc2NSA1Ljc2Njc0IDYuNjU4NDIgNS40NTQ4NkM2LjU4MjIgNS4xNTQ4NiA2LjI4NDcxIDQuNzA3OTEgNS4zNjIwMiA0LjM1NjQxQzUuMTUwNzggNC4yNzU5MyA0Ljk5NDcxIDQuMDkzOSA0Ljk0NzQzIDMuODcyODVDNC45MDAxNSAzLjY1MTggNC45NjgxIDMuNDIxODUgNS4xMjc5NCAzLjI2MjAxTDcuNTI3OTQgMC44NjIwMVpNNi43MjQzOCAzLjU1MTE5QzcuMzgwNTQgMy45NzI3NCA3Ljc5NDY2IDQuNTEyMzggNy45NTA3IDUuMTI2NTVDOC4xNjUyMSA1Ljk3MDkyIDcuODQ0NDEgNi43ODExNiA3LjMxMjQyIDcuMzEzMTVDNi43ODA0MyA3Ljg0NTE1IDUuOTcwMTkgOC4xNjU5NCA1LjEyNTgyIDcuOTUxNDNDNC41MTE2NSA3Ljc5NTQgMy45NzIwMSA3LjM4MTI3IDMuNTUwNDYgNi43MjUxMUwyLjI3NTQ5IDguMDAwMDhMNC4yMDQwOSA5LjkyODY4QzQuMzYzOTMgMTAuMDg4NSA0LjQzMTg4IDEwLjMxODUgNC4zODQ2IDEwLjUzOTVDNC4zMzczMiAxMC43NjA2IDQuMTgxMjUgMTAuOTQyNiAzLjk3MDAxIDExLjAyMzFDMy4wNDczMiAxMS4zNzQ2IDIuNzQ5ODMgMTEuODIxNSAyLjY3MzYxIDEyLjEyMTVDMi41OTQzOCAxMi40MzM0IDIuNzAyNzUgMTIuNzc3MyAyLjk2MjQyIDEzLjAzN0MzLjIyMjA5IDEzLjI5NjcgMy41NjYwMiAxMy40MDUxIDMuODc3OSAxMy4zMjU4QzQuMTc3OSAxMy4yNDk2IDQuNjI0ODUgMTIuOTUyMSA0Ljk3NjM2IDEyLjAyOTRDNS4wNTY4MyAxMS44MTgyIDUuMjM4ODYgMTEuNjYyMSA1LjQ1OTkxIDExLjYxNDhDNS42ODA5NiAxMS41Njc1IDUuOTEwOTEgMTEuNjM1NSA2LjA3MDc1IDExLjc5NTNMNy45OTkzNSAxMy43MjM5TDkuMjc0MzEgMTIuNDQ5QzguNjE4MTYgMTIuMDI3NCA4LjIwNDAzIDExLjQ4NzggOC4wNDggMTAuODczNkM3LjgzMzQ4IDEwLjAyOTIgOC4xNTQyOCA5LjIxOSA4LjY4NjI4IDguNjg3MDFDOS4yMTgyNyA4LjE1NTAxIDEwLjAyODUgNy44MzQyMiAxMC44NzI5IDguMDQ4NzNDMTEuNDg3IDguMjA0NzYgMTIuMDI2NyA4LjYxODg5IDEyLjQ0ODIgOS4yNzUwNUwxMy43MjMyIDguMDAwMDhMMTEuNzk0NiA2LjA3MTQ5QzExLjYzNDggNS45MTE2NCAxMS41NjY4IDUuNjgxNjkgMTEuNjE0MSA1LjQ2MDY1QzExLjY2MTQgNS4yMzk1OSAxMS44MTc0IDUuMDU3NTYgMTIuMDI4NyA0Ljk3NzA5QzEyLjk1MTQgNC42MjU1OSAxMy4yNDg5IDQuMTc4NjQgMTMuMzI1MSAzLjg3ODYzQzEzLjQwNDMgMy41NjY3NSAxMy4yOTU5IDMuMjIyODIgMTMuMDM2MyAyLjk2MzE1QzEyLjc3NjYgMi43MDM0OCAxMi40MzI3IDIuNTk1MTEgMTIuMTIwOCAyLjY3NDM1QzExLjgyMDggMi43NTA1NiAxMS4zNzM4IDMuMDQ4MDUgMTEuMDIyMyAzLjk3MDc0QzEwLjk0MTkgNC4xODE5OSAxMC43NTk4IDQuMzM4MDYgMTAuNTM4OCA0LjM4NTM0QzEwLjMxNzcgNC40MzI2MiAxMC4wODc4IDQuMzY0NjYgOS45Mjc5NCA0LjIwNDgyTDcuOTk5MzUgMi4yNzYyMkw2LjcyNDM4IDMuNTUxMTlaIiBmaWxsPSJjdXJyZW50Q29sb3IiLz4KICA8L2c+CiAgPGRlZnM+CiAgICA8Y2xpcFBhdGggaWQ9ImNsaXAwXzc0NzJfMTUxNSI+CiAgICAgIDxyZWN0IHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgZmlsbD0id2hpdGUiLz4KICAgIDwvY2xpcFBhdGg+CiAgPC9kZWZzPgo8L3N2Zz4="}),path:"/integrations",element:(0,E.jsx)(Y,{})},{key:"payments",label:(0,E.jsx)(aM.N_,{to:"/settings/payments",children:(0,D.__)("Payment Settings","formgent")}),icon:(0,E.jsx)(s.A,{src:a.A}),path:"/payments",element:(0,E.jsx)(K,{})}]);(0,j.LB)()&&o.push({key:"license-settings",label:(0,E.jsx)(aM.N_,{to:"/settings/license-settings",children:(0,D.__)("Activate License","formgent")}),icon:(0,E.jsx)(s.A,{src:"data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgdmlld0JveD0iMCAwIDE2IDE2IiBmaWxsPSJub25lIj4KICA8ZyBjbGlwLXBhdGg9InVybCgjY2xpcDBfNDk1MV82MzI4KSI+CiAgICA8cGF0aCBmaWxsLXJ1bGU9ImV2ZW5vZGQiIGNsaXAtcnVsZT0iZXZlbm9kZCIgZD0iTTcuNzAxNDYgMS40MDU2NEM4LjY2MDQ3IDAuODAzOTkyIDkuNzk1MjcgMC41NDU2MDYgMTAuOTIwMiAwLjY3Mjc1NkMxMi4wNDUyIDAuNzk5OTA2IDEzLjA5MzcgMS4zMDUwNiAxMy44OTQyIDIuMTA1NTlDMTQuNjk0NyAyLjkwNjExIDE1LjE5OTkgMy45NTQ2MSAxNS4zMjcgNS4wNzk1NkMxNS40NTQyIDYuMjA0NTEgMTUuMTk1OCA3LjMzOTMxIDE0LjU5NDEgOC4yOTgzMkMxMy45OTI1IDkuMjU3MzMgMTMuMDgzMiA5Ljk4Mzc3IDEyLjAxNSAxMC4zNTg4QzExLjA4MTggMTAuNjg2NSAxMC4wNzU1IDEwLjcyODkgOS4xMjMxOCAxMC40ODYxTDguODYxOTggMTAuNzQ3M0M4Ljg2MTk2IDEwLjc0NzMgOC44NjIgMTAuNzQ3MyA4Ljg2MTk4IDEwLjc0NzNDOC40ODcgMTEuMTIyMyA3Ljk3ODM0IDExLjMzMzIgNy40NDc5OCAxMS4zMzMzTDcuMzMzMTcgMTEuMzMzM1YxMS45OTk5QzcuMzMzMTcgMTIuMzUzNiA3LjE5MjcgMTIuNjkyNyA2Ljk0MjY1IDEyLjk0MjhDNi42OTI2IDEzLjE5MjggNi4zNTM0NiAxMy4zMzMzIDUuOTk5ODQgMTMuMzMzM0g1LjMzMzE3VjEzLjk5OTlDNS4zMzMxNyAxNC4zNTM2IDUuMTkyNjkgMTQuNjkyNyA0Ljk0MjY1IDE0Ljk0MjhDNC42OTI2IDE1LjE5MjggNC4zNTM0NiAxNS4zMzMzIDMuOTk5ODQgMTUuMzMzM0gxLjk5OTg0QzEuNjQ2MjIgMTUuMzMzMyAxLjMwNzA4IDE1LjE5MjggMS4wNTcwMyAxNC45NDI4QzAuODA2OTc5IDE0LjY5MjcgMC42NjY1MDQgMTQuMzUzNiAwLjY2NjUwNCAxMy45OTk5VjEyLjU1MTlDMC42NjY2MTcgMTIuMDIxNiAwLjg3NzM3OSAxMS41MTI5IDEuMjUyNDMgMTEuMTM3OUMxLjI1MjQxIDExLjEzNzkgMS4yNTI0NiAxMS4xMzc5IDEuMjUyNDMgMTEuMTM3OUw1LjUxMzcgNi44NzY2MUM1LjI3MDg0IDUuOTI0MyA1LjMxMzMyIDQuOTE4MDMgNS42NDA5OCAzLjk4NDc4QzYuMDE2MDEgMi45MTY1OSA2Ljc0MjQ1IDIuMDA3MjkgNy43MDE0NiAxLjQwNTY0Wk0xMC43NzA1IDEuOTk3NjVDOS45NDU1MSAxLjkwNDQxIDkuMTEzMzIgMi4wOTM4OSA4LjQxMDA1IDIuNTM1MUM3LjcwNjc3IDIuOTc2MzEgNy4xNzQwNSAzLjY0MzEzIDYuODk5MDMgNC40MjY0N0M2LjYyNCA1LjIwOTgxIDYuNjIyOTYgNi4wNjMzIDYuODk2MDcgNi44NDczMUM2Ljk4MDIgNy4wODg4MiA2LjkxODc1IDcuMzU3MTggNi43Mzc5MSA3LjUzODAyTDIuMTk1MjQgMTIuMDgwN0MyLjA3MDI0IDEyLjIwNTYgMS45OTk5MSAxMi4zNzUyIDEuOTk5ODQgMTIuNTUxOUMxLjk5OTg0IDEyLjU1MTkgMS45OTk4NCAxMi41NTIgMS45OTk4NCAxMi41NTE5VjEzLjk5OTlIMy45OTk4NFYxMy4zMzMzQzMuOTk5ODQgMTIuOTc5NyA0LjE0MDMxIDEyLjY0MDUgNC4zOTAzNiAxMi4zOTA1QzQuNjQwNDEgMTIuMTQwNCA0Ljk3OTU1IDExLjk5OTkgNS4zMzMxNyAxMS45OTk5SDUuOTk5ODRWMTEuMzMzM0M1Ljk5OTg0IDEwLjk3OTcgNi4xNDAzMSAxMC42NDA1IDYuMzkwMzYgMTAuMzkwNUM2LjY0MDQxIDEwLjE0MDQgNi45Nzk1NSA5Ljk5OTk1IDcuMzMzMTcgOS45OTk5NUg3LjQ0NzY5QzcuNDQ3NjUgOS45OTk5NSA3LjQ0Nzc0IDkuOTk5OTUgNy40NDc2OSA5Ljk5OTk1QzcuNjI0NDQgOS45OTk4NyA3Ljc5NDA3IDkuOTI5NjEgNy45MTkwMyA5LjgwNDYxTDguNDYxNzcgOS4yNjE4OEM4LjY0MjYxIDkuMDgxMDMgOC45MTA5NiA5LjAxOTU5IDkuMTUyNDggOS4xMDM3MkM5LjkzNjQ5IDkuMzc2ODIgMTAuNzkgOS4zNzU3OCAxMS41NzMzIDkuMTAwNzZDMTIuMzU2NyA4LjgyNTczIDEzLjAyMzUgOC4yOTMwMSAxMy40NjQ3IDcuNTg5NzRDMTMuOTA1OSA2Ljg4NjQ2IDE0LjA5NTQgNi4wNTQyOCAxNC4wMDIxIDUuMjI5MzFDMTMuOTA4OSA0LjQwNDM1IDEzLjUzODQgMy42MzU0NSAxMi45NTE0IDMuMDQ4NEMxMi4zNjQzIDIuNDYxMzQgMTEuNTk1NCAyLjA5MDkgMTAuNzcwNSAxLjk5NzY1WiIgZmlsbD0iIzVFNTNGOSIvPgogICAgPHBhdGggZD0iTTEwLjk5OTggNS4zMzMxN0MxMS4xODM5IDUuMzMzMTcgMTEuMzMzMiA1LjE4MzkzIDExLjMzMzIgNC45OTk4NEMxMS4zMzMyIDQuODE1NzQgMTEuMTgzOSA0LjY2NjUgMTAuOTk5OCA0LjY2NjVDMTAuODE1NyA0LjY2NjUgMTAuNjY2NSA0LjgxNTc0IDEwLjY2NjUgNC45OTk4NEMxMC42NjY1IDUuMTgzOTMgMTAuODE1NyA1LjMzMzE3IDEwLjk5OTggNS4zMzMxN1oiIGZpbGw9IiM1RTUzRjkiLz4KICAgIDxwYXRoIGZpbGwtcnVsZT0iZXZlbm9kZCIgY2xpcC1ydWxlPSJldmVub2RkIiBkPSJNMTEgNS4zMzMzM0MxMS4xODQxIDUuMzMzMzMgMTEuMzMzMyA1LjE4NDA5IDExLjMzMzMgNUMxMS4zMzMzIDQuODE1OTEgMTEuMTg0MSA0LjY2NjY3IDExIDQuNjY2NjdDMTAuODE1OSA0LjY2NjY3IDEwLjY2NjcgNC44MTU5MSAxMC42NjY3IDVDMTAuNjY2NyA1LjE4NDEgMTAuODE1OSA1LjMzMzMzIDExIDUuMzMzMzNaTTEwIDVDMTAgNC40NDc3MiAxMC40NDc3IDQgMTEgNEMxMS41NTIzIDQgMTIgNC40NDc3MiAxMiA1QzEyIDUuNTUyMjggMTEuNTUyMyA2IDExIDZDMTAuNDQ3NyA2IDEwIDUuNTUyMjggMTAgNVoiIGZpbGw9IiM1RTUzRjkiLz4KICA8L2c+CiAgPGRlZnM+CiAgICA8Y2xpcFBhdGggaWQ9ImNsaXAwXzQ5NTFfNjMyOCI+CiAgICAgIDxyZWN0IHdpZHRoPSIxNiIgaGVpZ2h0PSIxNiIgZmlsbD0id2hpdGUiLz4KICAgIDwvY2xpcFBhdGg+CiAgPC9kZWZzPgo8L3N2Zz4="}),path:"/license-settings",element:(0,E.jsx)(q.Slot,{children:M=>(0,E.jsx)(E.Fragment,{children:M})})});const U=()=>(0,i.getPlugins)().map((M,N)=>{const e=M.render;return(0,E.jsx)(e,{},N)});return(0,E.jsxs)(t.SlotFillProvider,{children:[(0,E.jsx)(U,{}),(0,E.jsxs)("div",{className:"formgent-editor-wrap",children:[(0,E.jsx)(eM,{}),(0,E.jsx)(B,{className:"formgent-editor-wrap__content",children:(0,E.jsx)(e,{children:(0,E.jsx)(N,{path:"/",element:(0,E.jsx)(cM,{NavLink:c,useNavigate:l,useLocation:I,navItems:o}),children:o.map(M=>(0,E.jsx)(N,{path:M.path,element:M.element},M.key))})})})]})]})}},62985(M){"use strict";M.exports="SECRET_DO_NOT_PASS_THIS_OR_YOU_WILL_BE_FIRED"},71120(M,N,e){"use strict";e.d(N,{A:()=>t}),e(51609);const t="data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMTkiIGhlaWdodD0iMjIiIHZpZXdCb3g9IjAgMCAxOSAyMiIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiBjbGFzcz0iZm9ybWdlbnQtc3ZnLXN0cm9rZS1vbmx5Ij4KPHBhdGggZD0iTTMuMjk0MyAzLjgzNUwxLjE2NTMgMTYuNjcxQzAuOTg1Mjk4IDE3Ljc1MiAwLjg5NjI5OCAxOC4yOTMgMS4xOTUzIDE4LjY0NkMxLjQ5MjMgMTkgMi4wMzczIDE5IDMuMTI4MyAxOUgzLjUzMDNDNC4zNTMzIDE5IDQuNzY0MyAxOSA1LjA0NTMgMTguNzU2QzUuMzI1MyAxOC41MTEgNS4zODQzIDE4LjEwMiA1LjUwMDMgMTcuMjgzTDUuOTY4MyAxMy45OTNDNi4wMDUzIDEzLjczMyA2LjAyMzMgMTMuNjAzIDYuMDUyMyAxMy40OTJDNi4xNTI3OSAxMy4xMDMgNi4zNjg2OCAxMi43NTM1IDYuNjcxNTggMTIuNDg5NUM2Ljk3NDQ4IDEyLjIyNTUgNy4zNTAxOSAxMi4wNTk0IDcuNzQ5MyAxMi4wMTNDNy44NjIzIDEyIDcuOTkzMyAxMiA4LjI1NDMgMTJIOS40MTYzQzEwLjkyMjMgMTEuOTk3NCAxMi4zODI3IDExLjQ4MjUgMTMuNTU3MyAxMC41NDAxQzE0LjczMiA5LjU5NzYyIDE1LjU1MTIgOC4yODM2MyAxNS44ODAzIDYuODE0QzE2LjU1NDMgMy44MzYgMTQuMzAyMyAxIDExLjI2MzMgMUg2LjYyMzNDNS41MDkzIDEgNC45NTMzIDEgNC41MTMzIDEuMjM1QzQuMjYzMyAxLjM2OSA0LjA0MzMgMS41NTUgMy44NzEzIDEuNzgyQzMuNTY4MyAyLjE3OSAzLjQ3NzMgMi43MzEgMy4yOTQzIDMuODM1WiIgc3Ryb2tlPSJibGFjayIgc3Ryb2tlLXdpZHRoPSIxLjUiIHN0cm9rZS1saW5lY2FwPSJyb3VuZCIgc3Ryb2tlLWxpbmVqb2luPSJyb3VuZCIvPgo8cGF0aCBkPSJNNS4yNDI1OSAxOC41TDUuMDE0NTkgMTkuODMyQzQuOTA5NTkgMjAuNDQyIDUuMzg0NTkgMjEgNi4wMTA1OSAyMUg4LjAwMDU5QzguNDk1NTkgMjEgOC45MTc1OSAyMC42NDYgOC45OTg1OSAyMC4xNjRMOS43Mjg1OSAxNS44MzVDOS44MDg1OSAxNS4zNTMgMTAuMjMxNiAxNSAxMC43MjU2IDE1SDEyLjUyODZDMTUuMTA5NiAxNSAxNy4zNDQ2IDEzLjIyNyAxNy45MDQ2IDEwLjczNUMxOC4yOTY2IDguOTkgMTcuNDQzNiA3LjMxIDE1Ljk5OTYgNi41IiBzdHJva2U9ImJsYWNrIiBzdHJva2Utd2lkdGg9IjEuNSIgc3Ryb2tlLWxpbmVjYXA9InJvdW5kIiBzdHJva2UtbGluZWpvaW49InJvdW5kIi8+Cjwvc3ZnPgo="}}]);