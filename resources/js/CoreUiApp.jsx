import {
    CBadge,
    CButton,
    CCard,
    CCardBody,
    CCardText,
    CCardTitle,
    CCol,
    CContainer,
    CListGroup,
    CListGroupItem,
    CRow,
} from '@coreui/react';

const translations = window.nomaTranslations?.welcome ?? {
    badge: 'CoreUI + React',
    title: 'CoreUI ir uzstādīts šajā Laravel projektā.',
    description:
        'Šī lapa tiek renderēta ar React un stilizēta ar CoreUI komponentēm, tāpēc frontend daļa ir gatava nākamajiem ekrāniem.',
    checklist: [
        'React ieejas punkts darbojas caur Vite',
        'CoreUI stili ir ielādēti aktīvu būvēšanas plūsmā',
        'Komponenšu bibliotēka ir gatava Blade lapām vai SPA paplašināšanai',
    ],
    coreui_docs: 'CoreUI dokumentācija',
    react_docs: 'React dokumentācija',
};

export default function CoreUiApp() {
    return (
        <div className="d-flex min-vh-100 align-items-center py-5">
            <CContainer>
                <CRow className="justify-content-center">
                    <CCol md={10} lg={8} xl={7}>
                        <CCard className="border-0 shadow-lg">
                            <CCardBody className="p-4 p-md-5">
                                <CBadge color="primary" className="mb-3 text-uppercase">
                                    {translations.badge}
                                </CBadge>
                                <CCardTitle className="fs-1 mb-3">
                                    {translations.title}
                                </CCardTitle>
                                <CCardText className="text-body-secondary fs-5 mb-4">
                                    {translations.description}
                                </CCardText>

                                <CListGroup flush className="mb-4">
                                    {translations.checklist.map((item) => (
                                        <CListGroupItem key={item} className="px-0">
                                            {item}
                                        </CListGroupItem>
                                    ))}
                                </CListGroup>

                                <div className="d-flex flex-wrap gap-3">
                                    <CButton color="primary" href="https://coreui.io/react/docs/" target="_blank" rel="noreferrer">
                                        {translations.coreui_docs}
                                    </CButton>
                                    <CButton color="secondary" variant="outline" href="https://react.dev" target="_blank" rel="noreferrer">
                                        {translations.react_docs}
                                    </CButton>
                                </div>
                            </CCardBody>
                        </CCard>
                    </CCol>
                </CRow>
            </CContainer>
        </div>
    );
}
