/**
 * External dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { __ } from '@wordpress/i18n';
import {
	ToggleControl, Button,
	Panel, PanelBody, PanelRow,
	Notice, NoticeList, NoticeListProps,
	SelectControl, Spinner
} from '@wordpress/components';
import * as Woo from '@woocommerce/components';
import { Fragment, useState, useEffect } from '@wordpress/element';
import * as WP from '@wordpress/element';
import axios from 'axios';
import { getOptions, saveOptions } from './utils/optionsApiClient';

/**
 * Internal dependencies
 */
import './index.scss';

const FilterShippingRatesSettingsPage = () => {
	const [active, setActive] = useState(false);
	const [orderStatus, setOrderStatus] = useState(false);
	const [notices, setNotices] = useState([]);
	const [isLoading, setIsLoading] = useState(true);

	const addNotice = ( newNotice ) => {
		// Verificăm dacă un element cu același ID există deja în array
		if (notices.find(notice => notice.id === newNotice.id)) {
			console.log(`Notice with id ${newNotice.id} already exists.`);
			return;
		}

		// Adăugăm noul element în array
		setNotices([...notices, newNotice]);
	};

	const removeNotice = ( id ) => {
		setNotices(notices.filter(notice => notice.id !== id));
	};

	const resetNotices = () => {
		setNotices([]);
	};

	useEffect(() => {

		getOptions().then((options) => {
			if( options?.active === '1' )
				setActive(true);

			if( options?.orderStatus )
				setOrderStatus( JSON.parse(options.orderStatus) )

			setIsLoading(false);
		}).catch(error => {
			// Tratează eroarea
			addNotice({
				id: 'options-notice-error',
				content: __('There was a problem with your fetch operation:'+ error, 'filter-shipping-rates'),
				status: 'error',
			});
		});

	}, []); // Only run once on mount

	const handleSaveButton = (value) => {
		const options = {
			options : [
				{
					option_key: 'active',
					option_value: active ? '1' : '0',
				},
				{
					option_key: 'orderStatus',
					option_value: orderStatus ? JSON.stringify(orderStatus) : '[]',
				},
			]
		};

		saveOptions(options).then((response) => {
			if (response === true) {
				addNotice({
					id: 'save-notice-success',
					content: __('Setările au fost salvate cu success!', 'filter-shipping-rates'),
					status: 'success',
				});
			}
		}).catch(error => {
			// Tratează eroarea
			addNotice({
				id: 'save-notice-error',
				content: __('Există o problemă la salvarea datelor:'+ error, 'filter-shipping-rates'),
				status: 'error',
			});
		});
	}

    return (
    <Fragment>
        <Woo.Section component="article">
			<NoticeList className="filter-shipping-rates" notices={notices} onRemove={removeNotice}/>
			<Panel header={ __('Setări', 'filter-shipping-rates')}>
				<React.Fragment key=".0">
					<PanelBody title={ __('Ascunde plată ramburs', 'filter-shipping-rates') }>
						<PanelRow className={"filter-shipping-rates"}>
							{isLoading ? (
								<Spinner />
							) : (
								<>
									<ToggleControl
										label="Activează"
										checked={active}
										onChange={setActive}
									/>
									<SelectControl
										help={__( 'Utilizatori care au comenzi cu statusurile selectate nu vor putea folosi metoda de plată la livrare.',"filter-shipping-rates")}
										label={__('Selectează statusul comenzi', 'filter-shipping-rates')}
										onBlur={function noRefCheck(){}}
										onChange={setOrderStatus}
										onFocus={function noRefCheck(){}}
										multiple={true}
										value={orderStatus}
										options={[
											{
												label: __('Anulată','filter-shipping-rates'),
												value: 'cancelled'
											},
											{
												label: __('Eșuată','filter-shipping-rates'),
												value: 'failed'
											},
										]}
									/>
								</>
							)}
						</PanelRow>
					</PanelBody>
				</React.Fragment>
			</Panel>

			<Button
				variant="primary"
				onClick={handleSaveButton}
				className={'filter-options-save'}
			>
				Salvează
			</Button>
        </Woo.Section>
    </Fragment>
)};

addFilter('woocommerce_admin_pages_list', 'filter-shipping-rates', (pages) => {
    pages.push({
        container: FilterShippingRatesSettingsPage,
        path: '/filter-shipping-rates',
        breadcrumbs: [__('Setări plată ramburs', 'filter-shipping-rates')],
        navArgs: {
            id: 'filter_shipping_rates',
        },
    });

    return pages;
});
