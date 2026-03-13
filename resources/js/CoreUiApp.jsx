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

const checklistItems = [
    'React entrypoint mounted through Vite',
    'CoreUI styles loaded in the asset pipeline',
    'Component library ready for Blade-backed pages or SPA expansion',
];

export default function CoreUiApp() {
    return (
        <div className="d-flex min-vh-100 align-items-center py-5">
            <CContainer>
                <CRow className="justify-content-center">
                    <CCol md={10} lg={8} xl={7}>
                        <CCard className="border-0 shadow-lg">
                            <CCardBody className="p-4 p-md-5">
                                <CBadge color="primary" className="mb-3 text-uppercase">
                                    CoreUI + React
                                </CBadge>
                                <CCardTitle className="fs-1 mb-3">
                                    CoreUI has been installed on this Laravel project.
                                </CCardTitle>
                                <CCardText className="text-body-secondary fs-5 mb-4">
                                    This page is rendered by React and styled with CoreUI components, so the
                                    frontend stack is ready for additional screens.
                                </CCardText>

                                <CListGroup flush className="mb-4">
                                    {checklistItems.map((item) => (
                                        <CListGroupItem key={item} className="px-0">
                                            {item}
                                        </CListGroupItem>
                                    ))}
                                </CListGroup>

                                <div className="d-flex flex-wrap gap-3">
                                    <CButton color="primary" href="https://coreui.io/react/docs/" target="_blank" rel="noreferrer">
                                        CoreUI docs
                                    </CButton>
                                    <CButton color="secondary" variant="outline" href="https://react.dev" target="_blank" rel="noreferrer">
                                        React docs
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
