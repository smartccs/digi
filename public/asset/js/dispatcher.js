'use strict';

class DispatcherPanel extends React.Component {
    componentDidMount() {
        this.requestPoll();
    }

    requestPoll(){
        console.log('Polling');
    }
    render() {
        <h1>Hi</h1>
    }
};

ReactDOM.render(
    <DispatcherPanel />,
    document.getElementById('dispatcher-panel')
);