// (function($) {
//     // jQuery plugin definition
//     $.incoming = function(params) {

//         // merge default and user parameters
//         params = $.extend( {url: '/incoming', 'modal': '#modal-incoming'}, params);

//         $.ajax({
//                 url: params.url,
//                 type: 'get',
//                 data: {},
//                 success: function (data) {
//                     console.log(data);
//                     $(params.modal+" #user-image").prop("style", "background-image: url(img/img1.png);");
//                 }
//             });

//         return this;

//     };

// })(jQuery);
'use strict';

class ModalContainer extends React.Component {

    componentWillMount() {
        this._getIncomingRequests();
        this.state = {
            request: {
                user: {
                    picture: 'logo.png',
                    first_name: 'John',
                    last_name: 'Doe'
                }
            }
        };
    }

    _getIncomingRequests() {
        console.log('TEsting');
        var latitude;
        var longitude;
        
        function getLocation() {
            if (navigator.geolocation) {
                navigator.geolocation.getCurrentPosition(showPosition);
            } else {
                latitude = 0;
                longitude = 0;
            }
        }
        
        function showPosition(position) {
            latitude = position.coords.latitude;
            longitude = position.coords.longitude; 
        }

        $.ajax({
            url: '/provider/incoming',
            dataType: "JSON",
            headers: {'X-CSRF-TOKEN': window.Laravel.csrfToken },
            data: {
                latitude: latitude,
                longitude: longitude
            },
            type: "GET",
            success: function(data){
                // this.setState({account_status: data.account_status});
                // this.setState({service_status: data.service_status});
                console.log(data);
                console.log('length', data.requests.length);
                if(data.requests.length > 0 && data.requests[0].request.status == 'SEARCHING') {
                    console.log('data.requests[0]', data.requests[0].request);
                    this.setState({request: data.requests[0].request});
                    this._open();
                } else {
                    // this._close();
                }
                setTimeout(this._getIncomingRequests, 5000);
            }.bind(this)
        });
    }

    _accept(event) {
        event.preventDefault();
        console.log('Accept');
        $.ajax({
            url: '/provider/request/accept',
            dataType: 'json',
            type: 'POST',
            data: donation,
            success: function(data) {
                this.setState({data: data});
            }.bind(this),
            error: function(xhr, status, err) {
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
        this._close();
    }

    _reject(event) {
        event.preventDefault();
        console.log('Reject');
        $.ajax({
            url: '/provider/request/reject',
            dataType: 'json',
            type: 'POST',
            data: donation,
            success: function(data) {
                this.setState({data: data});
            }.bind(this),
            error: function(xhr, status, err) {
                console.error(this.props.url, status, err.toString());
            }.bind(this)
        });
        this._close();
    }

    _open() {
        console.log('Modal Show');
        $("#incoming").modal('show');
    }

    _close() {
        console.log('Modal Hide');
        $("#incoming").hide('hide');
    }

    render() {
        console.log('render', this);
        console.log('render', this.state.request);
        console.log('this.state.request.user.picture', this.state.request.user.picture);
        let picture = this.state.request.user.picture == null ? 'asset/logo.png' : this.state.request.user.picture;
        return (
            <div className="modal fade" id="incoming" role="dialog">
                <div className="modal-dialog" role="document">
                    <div className="modal-content">
                        <div className="modal-header">
                            <h4 className="modal-title text-center incoming-tit" id="myModalLabel">Incoming Request</h4>
                        </div>
                        <div className="modal-body">
                            <div className="incoming-img bg-img" id="user-image" style={{ backgroundImage: 'url(' + picture + ')'}}></div>
                            <div className="text-center">
                                <h3 id="usser-name">{this.state.request.user.first_name} {this.state.request.user.last_name}</h3>
                            </div>
                        </div>
                        <div className="modal-footer row no-margin">
                            <button type="button" className="btn btn-primary incoming-btn" onClick={this._accept.bind(this)}>Accept</button>
                            <button type="button" className="btn btn-default incoming-btn" onClick={this._reject.bind(this)} data-dismiss="modal">Cancel</button>
                        </div>
                    </div>
                </div>
            </div>
        );
    }
};


ReactDOM.render(
    <ModalContainer />,
    document.getElementById('modal-incoming')
)